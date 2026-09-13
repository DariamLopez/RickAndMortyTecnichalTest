# Rick and Morty App

Full-stack technical test: a Laravel API that syncs data from the public [Rick and Morty API](https://rickandmortyapi.com) into its own database, exposes a REST API combining that data with user functionality (auth + favorites), and a decoupled Vue 3 SPA that consumes only that own API.

## Stack

- **Backend:** Laravel 13, PHP 8.5, MySQL 8.4, custom token authentication (no Sanctum).
- **Frontend:** Vue 3, Vue Router, Pinia, Vuetify, TypeScript, Axios (behind an internal adapter).
- **Environment:** Docker / Laravel Sail.
- **Tests:** PHPUnit (backend), Vitest (frontend).

## Getting started (Docker / Laravel Sail)

```bash
cp .env.example .env
./vendor/bin/sail up -d
```

That's it — the container's entrypoint ([`docker/entrypoint.sh`](./docker/entrypoint.sh)) automatically installs Composer/npm dependencies, generates `APP_KEY` if missing, runs migrations, and builds the frontend assets on first boot (each step is skipped on later restarts once it's already done). Then visit `http://localhost`, and run the sync command once to populate the database:

```bash
./vendor/bin/sail artisan rickandmorty:sync
```

### Fallback if `sail` fails on Windows (WSL with no proper default distro)

On Windows, `vendor/bin/sail` is a bash script executed through WSL interop. If your default WSL distro isn't a full Linux distro (for example, if `wsl --status` shows `docker-desktop`, which is Docker Desktop's internal VM and doesn't ship a general-purpose `bash`), the script fails with an error like `execvpe /bin/bash failed`.

This is a local environment quirk, not a project issue: `compose.yaml` is the same file Sail uses internally, so it can be brought up equivalently with `docker compose` directly:

```powershell
docker compose up -d --build
docker compose exec laravel.test php artisan rickandmorty:sync
```

To make `sail` work natively, install a real Linux distro and set it as default:

```powershell
wsl --install -d Ubuntu
wsl --set-default Ubuntu
```

### Frontend dev server (hot reload)

Vite can run either inside the container or on the host; both work since assets are served independently from the PHP app:

```powershell
npm install
npm run dev
```

## Deployment to a restricted-port server

Some hosts (e.g. a shared company server) require every exposed port to fall within a specific range and SSH key-based access. `compose.yaml` already reads all ports from `.env`, so nothing needs to change there — only the values below:

```env
APP_PORT=6010
VITE_PORT=6011
FORWARD_DB_PORT=6012
APP_URL=http://<server-host-or-ip>:6010
VITE_API_BASE_URL="${APP_URL}/api"
```

Deploying is then just:

```bash
ssh -i ~/.ssh/id_ed25519 -p <ssh-port> user@host
git clone <repo> && cd <repo>
cp .env.example .env   # adjust the ports/host above
docker compose up -d --build
```

The entrypoint script handles the rest (Composer, `APP_KEY`, migrations, npm build) — no need to have PHP, Composer, or Node installed on the host itself, since everything runs inside the container.

## Running the sync command

```bash
php artisan rickandmorty:sync
```

- Idempotent: running it multiple times never duplicates rows or pivot relations (`updateOrCreate` + `sync()`, matched on each entity's `external_id`).
- Paginates through the full external API (locations → episodes → characters, in that order, since characters reference the other two).
- Tolerates partial failures: if one entity's pagination fails (network error, invalid shape), it's logged and skipped; the other entities still sync. The command exits with code `1` if anything failed, `0` otherwise.
- Persists the raw external JSON payload (`raw_external_payloads` table) before transforming it, so data can be reprocessed without re-hitting the external API.

## Tests

Backend (isolated from the real external API via `Http::fake()`):
```bash
php artisan test
```

Frontend:
```bash
npm run test:unit
npm run types:check
```

## API documentation

See [`openapi.yaml`](./openapi.yaml) for the full OpenAPI 3.0 spec (endpoints, filters, request/response schemas, auth, error format). Paste it into https://editor.swagger.io for a readable view. Or use 'docs/api' endpoint.

## Design decisions

- **No Inertia.** The starter kit ships with Inertia + Fortify (session-based auth, server-rendered pages). Since the test explicitly requires a decoupled SPA with token-based auth and client-side routing, Inertia/Fortify/passkeys/wayfinder were removed in favor of a plain Laravel JSON API + a separate Vue Router SPA.
- **Custom token authentication, no Sanctum.** A `personal_access_tokens` table stores only the SHA-256 hash of each token (never the plaintext); a custom `TokenGuard` (implementing `Illuminate\Contracts\Auth\Guard`) resolves the user from the `Authorization: Bearer` header.
- **Decoupled external API client.** `RickAndMortyClient` (`app/Services/RickAndMorty`) is the only place that knows about the provider's HTTP shape. It validates the response shape before mapping, retries on connection errors/5xx (never on 4xx), and maps everything into immutable DTOs (`CharacterData`, `EpisodeData`, `LocationData`) that carry the provider's `external_id`, never our own database `id` — the translation from `external_id` to internal `id` happens only in the sync command, which is the only piece that knows both worlds.
- **Idempotent sync via natural keys.** Every synced entity has a unique `external_id`; the command always resolves rows via `updateOrCreate(['external_id' => ...], [...])` and syncs the `character_episode` pivot with `sync()` instead of `attach()`, so re-running the command is always safe.
- **PHP backed enums for closed-value fields.** `Character::status` and `Character::gender` are cast to `CharacterStatus`/`CharacterGender` backed enums. The sync command uses `tryFrom() ?? Unknown` when mapping external data, so an unexpected value from the provider never breaks persistence.
- **Homogeneous API errors.** All exceptions render as `{"message": ..., "errors"?: ...}` with correct status codes (401 unauthenticated, 404 not found, 422 validation), configured centrally in `bootstrap/app.php`.
- **Frontend layered architecture.** Components/views → Pinia stores → **Repositories** (`authRepository`, `characterRepository`, `favoriteRepository`, one per domain area) → **`HttpClient` contract** → `AxiosHttpClient` adapter. Nothing outside `lib/http/AxiosHttpClient.ts` knows Axios exists; swapping HTTP libraries only means writing a new class implementing `HttpClient`. All API errors are normalized to a single `ApiError` type (message/status/field errors) before they ever reach a store or component.
- **Filters reflected in the URL** (`?name=&status=&page=`), debounced, so a filtered/paginated view can be shared or reloaded without losing state.
- **Optimistic favorites.** Adding/removing a favorite updates the UI immediately and rolls back only if the request fails, avoiding a loading spinner for a simple toggle.
- **Reusable layout.** `layouts/AppLayout.vue` has no knowledge of Pinia or this app's routes — it receives a title, nav items, and slots. `App.vue` is the only file that wires it to this project's auth store and route names.
- **Vuetify** was chosen over Tailwind/shadcn (the starter kit's default) per prior experience with the library.


