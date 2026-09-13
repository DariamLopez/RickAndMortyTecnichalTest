#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    echo "No .env found, copying .env.example..."
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --no-progress
fi

if ! grep -q "^APP_KEY=base64" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

echo "Running migrations..."
php artisan migrate --force

# Uncomment to also populate the database automatically on first boot.
# This hits the real external API, so it's opt-in rather than run on every restart.
# if [ "$(php artisan tinker --execute='echo App\Models\Character::count();')" = "0" ]; then
#     php artisan rickandmorty:sync
# fi

if [ ! -d node_modules ]; then
    echo "Installing frontend dependencies..."
    npm install
fi

if [ ! -f public/build/manifest.json ]; then
    echo "Building frontend assets..."
    npm run build
fi

echo "Setup complete. Starting the application..."
exec start-container
