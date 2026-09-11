# Rick and Morty App

> Este README se completará por fases; de momento documenta el arranque del entorno.

## Entorno de desarrollo (Docker / Laravel Sail)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

### Alternativa si `sail` falla en Windows (WSL sin distro por defecto)

En Windows, `vendor/bin/sail` es un script bash que se ejecuta vía interoperabilidad con WSL. Si tu distro WSL por defecto no es una distro Linux completa (por ejemplo, si aparece `docker-desktop` en `wsl --status`, que es la VM interna de Docker Desktop y no trae `bash` de uso general), el script falla con un error del tipo `execvpe /bin/bash failed`.

Esto es una particularidad del entorno local, no del proyecto: el `compose.yaml` es el mismo que usa Sail internamente, así que se puede levantar de forma equivalente con `docker compose` directamente:

```powershell
docker compose up -d --build
docker compose exec laravel.test php artisan migrate
```

Para que `sail` funcione de forma nativa, basta con instalar una distro Linux real y marcarla como predeterminada, por ejemplo:

```powershell
wsl --install -d Ubuntu
wsl --set-default Ubuntu
```
