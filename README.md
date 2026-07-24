# Laravel Loadout

Build, save and share your Laravel starter stack.

Laravel Loadout is a free builder for assembling a Laravel project baseline. Choose the framework and runtime options you need, add curated or custom packages, then download a small starter archive or share a locked stack with a permanent URL.

## What It Generates

Each loadout produces an archive without `vendor` or `node_modules`:

- `composer.json`
- `package.json`
- `compose.yaml`
- `.env.loadout`
- `README.md`
- `resources/js/echo.ts` when Laravel Reverb is selected

The generated manifests reflect the selected Laravel and PHP versions, package manager, runtime services, and foundation packages. Shared loadouts are locked when published; clone one to make changes.

## Foundation Options

- Laravel Sanctum for API authentication
- Laravel Reverb, including Echo and Pusher frontend dependencies
- Laravel Scout
- Laravel Horizon with Redis
- Laravel Telescope or Laravel Pulse

## Local Development

Requirements: Docker, Composer, PHP 8.5, and Yarn.

```bash
composer install
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail yarn install
./vendor/bin/sail yarn dev
```

The application is available at `http://localhost`. Mailpit is available at `http://localhost:8025`.

## Verification

```bash
./vendor/bin/sail artisan test --compact
./vendor/bin/sail bin pint --dirty --format agent
./vendor/bin/sail yarn types:check
./vendor/bin/sail yarn build
```

## License

Laravel Loadout is released under the MIT License.
