<?php

namespace App\Services;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use Illuminate\Support\Collection;

class ArtifactGenerator
{
    /**
     * @return array<int, array{name: string, language: string, content: string}>
     */
    public function preview(Loadout $loadout): array
    {
        $packages = $loadout->packages()
            ->orderBy('position')
            ->get();

        $artifacts = [
            [
                'name' => 'composer.json',
                'language' => 'json',
                'content' => $this->composerJson($loadout, $packages),
            ],
            [
                'name' => 'package.json',
                'language' => 'json',
                'content' => $this->packageJson($loadout),
            ],
            [
                'name' => 'compose.yaml',
                'language' => 'yaml',
                'content' => $this->composeYaml($loadout, $packages),
            ],
            [
                'name' => '.env.loadout',
                'language' => 'dotenv',
                'content' => $this->envLoadout($loadout),
            ],
            [
                'name' => 'README.md',
                'language' => 'markdown',
                'content' => $this->readme($loadout, $packages),
            ],
        ];

        if ($this->foundation($loadout, 'broadcasting') === 'reverb') {
            $artifacts[] = [
                'name' => 'resources/js/echo.ts',
                'language' => 'typescript',
                'content' => $this->echoConfiguration(),
            ];
        }

        return $artifacts;
    }

    /**
     * @param  Collection<int, LoadoutPackage>  $packages
     */
    private function composerJson(Loadout $loadout, Collection $packages): string
    {
        $require = [
            'php' => $this->builderValue($loadout, 'project.php_version', '^8.4'),
            'laravel/framework' => $this->builderValue($loadout, 'project.laravel_version', '^13.0'),
            'laravel/tinker' => '^2.10',
        ];
        $requireDev = [
            'laravel/pint' => '^1.25',
            'phpunit/phpunit' => '^12.0',
        ];

        if ($this->builderValue($loadout, 'runtime.local_runtime', 'sail') === 'sail') {
            $requireDev['laravel/sail'] = '^1.48';
        }

        $foundationPackages = $this->foundationComposerPackages($loadout);

        foreach ($foundationPackages as $package) {
            $require[$package] = '*';
        }

        foreach ($packages as $package) {
            if ($package->composer_name) {
                if (in_array($package->composer_name, $foundationPackages, true)) {
                    continue;
                }

                if ($this->isDevDependency($package)) {
                    $requireDev[$package->composer_name] = '*';
                } else {
                    $require[$package->composer_name] = '*';
                }
            }
        }

        $scripts = [
            'post-autoload-dump' => [
                'Illuminate\\Foundation\\ComposerScripts::postAutoloadDump',
                '@php artisan package:discover --ansi',
            ],
            'test' => '@php artisan test',
        ];

        if ($this->foundation($loadout, 'broadcasting') === 'reverb') {
            $scripts['reverb'] = '@php artisan reverb:start';
        }

        return $this->prettyJson([
            'name' => $this->composerPackageName($loadout),
            'type' => 'project',
            'description' => $loadout->description ?: 'Generated Laravel Loadout starter.',
            'require' => $require,
            'require-dev' => $requireDev,
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Database\\Factories\\' => 'database/factories/',
                    'Database\\Seeders\\' => 'database/seeders/',
                ],
            ],
            'scripts' => $scripts,
        ]);
    }

    private function packageJson(Loadout $loadout): string
    {
        $frontendStack = $this->builderValue($loadout, 'starter.frontend_stack', 'react-inertia');
        $packageManager = $this->packageManager($loadout);
        $dependencies = [
            'vite' => '^8.0.0',
            'typescript' => '^5.9.0',
        ];
        $devDependencies = ['tailwindcss' => '^4.0.0'];

        if ($this->foundation($loadout, 'broadcasting') === 'reverb') {
            $devDependencies['laravel-echo'] = '^2.0.0';
            $devDependencies['pusher-js'] = '^8.4.0';
        }

        if ($frontendStack === 'react-inertia') {
            $dependencies['@inertiajs/react'] = '^3.0.0';
            $dependencies['@vitejs/plugin-react'] = '^5.0.0';
            $dependencies['react'] = '^19.0.0';
            $dependencies['react-dom'] = '^19.0.0';
        }

        if ($frontendStack === 'vue-inertia') {
            $dependencies['@inertiajs/vue3'] = '^3.0.0';
            $dependencies['@vitejs/plugin-vue'] = '^6.0.0';
            $dependencies['vue'] = '^3.5.0';
        }

        return $this->prettyJson([
            'private' => true,
            'type' => 'module',
            'scripts' => [
                'build' => 'vite build',
                'dev' => 'vite --host 0.0.0.0',
            ],
            'dependencies' => $dependencies,
            'devDependencies' => $devDependencies,
            'packageManager' => $this->packageManagerSpecification($packageManager),
        ]);
    }

    /**
     * @param  Collection<int, LoadoutPackage>  $packages
     */
    private function composeYaml(Loadout $loadout, Collection $packages): string
    {
        $services = $this->requiredServices($packages);
        $database = $this->builderValue($loadout, 'runtime.database', 'mysql');
        $mail = $this->builderValue($loadout, 'runtime.mail', 'mailpit');

        if ($this->builderValue($loadout, 'runtime.cache', 'database') === 'redis') {
            $services[] = 'redis';
        }

        if ($this->builderValue($loadout, 'runtime.queue', 'database') === 'redis') {
            $services[] = 'redis';
        }

        if ($this->builderValue($loadout, 'runtime.search', 'none') === 'meilisearch') {
            $services[] = 'meilisearch';
        }

        if ($this->foundation($loadout, 'queue_monitoring') === 'horizon') {
            $services[] = 'redis';
        }

        if ($mail === 'mailpit') {
            $services[] = 'mailpit';
        }

        $services = array_values(array_unique($services));

        $ports = ['      - "${APP_PORT:-80}:80"'];

        if ($this->foundation($loadout, 'broadcasting') === 'reverb') {
            $ports[] = '      - "${FORWARD_REVERB_PORT:-8080}:8080"';
        }

        $yaml = [
            'services:',
            '  laravel.test:',
            '    build:',
            '      context: ./vendor/laravel/sail/runtimes/'.$this->sailPhpVersion($loadout),
            '      dockerfile: Dockerfile',
            '    ports:',
            ...$ports,
            '    environment:',
            '      LARAVEL_SAIL: 1',
            '    volumes:',
            '      - ".:/var/www/html"',
        ];

        if ($database === 'mysql') {
            $yaml = array_merge($yaml, [
                '  mysql:',
                '    image: "mysql:8.4"',
                '    ports:',
                '      - "${FORWARD_DB_PORT:-3306}:3306"',
                '    environment:',
                '      MYSQL_DATABASE: "${DB_DATABASE}"',
                '      MYSQL_USER: "${DB_USERNAME}"',
                '      MYSQL_PASSWORD: "${DB_PASSWORD}"',
                '      MYSQL_ROOT_PASSWORD: "${DB_PASSWORD}"',
            ]);
        }

        if ($database === 'pgsql') {
            $yaml = array_merge($yaml, [
                '  pgsql:',
                '    image: "postgres:17"',
                '    ports:',
                '      - "${FORWARD_DB_PORT:-5432}:5432"',
                '    environment:',
                '      POSTGRES_DB: "${DB_DATABASE}"',
                '      POSTGRES_USER: "${DB_USERNAME}"',
                '      POSTGRES_PASSWORD: "${DB_PASSWORD}"',
            ]);
        }

        if (in_array('redis', $services, true)) {
            $yaml = array_merge($yaml, [
                '  redis:',
                '    image: "redis:alpine"',
                '    ports:',
                '      - "${FORWARD_REDIS_PORT:-6379}:6379"',
            ]);
        }

        if (in_array('meilisearch', $services, true)) {
            $yaml = array_merge($yaml, [
                '  meilisearch:',
                '    image: "getmeili/meilisearch:latest"',
                '    ports:',
                '      - "${FORWARD_MEILISEARCH_PORT:-7700}:7700"',
            ]);
        }

        if (in_array('mailpit', $services, true)) {
            $yaml = array_merge($yaml, [
                '  mailpit:',
                '    image: "axllent/mailpit:latest"',
                '    ports:',
                '      - "${FORWARD_MAILPIT_PORT:-1025}:1025"',
                '      - "${FORWARD_MAILPIT_DASHBOARD_PORT:-8025}:8025"',
            ]);
        }

        return implode("\n", $yaml)."\n";
    }

    /**
     * @param  Collection<int, LoadoutPackage>  $packages
     * @return array<int, string>
     */
    private function requiredServices(Collection $packages): array
    {
        $services = [];

        foreach ($packages as $package) {
            $snapshot = $package->package_snapshot;

            if (! is_array($snapshot) || ! isset($snapshot['required_services']) || ! is_array($snapshot['required_services'])) {
                continue;
            }

            foreach ($snapshot['required_services'] as $service) {
                if (is_string($service) && $service !== '') {
                    $services[] = $service;
                }
            }
        }

        return array_values(array_unique($services));
    }

    private function envLoadout(Loadout $loadout): string
    {
        $appName = $this->builderValue($loadout, 'project.app_name', $loadout->title);
        $database = $this->builderValue($loadout, 'runtime.database', 'mysql');
        $mail = $this->builderValue($loadout, 'runtime.mail', 'mailpit');

        return implode("\n", [
            'APP_NAME="'.$appName.'"',
            'APP_ENV=local',
            'APP_KEY=',
            'APP_DEBUG=true',
            'APP_URL=http://localhost',
            '',
            'DB_CONNECTION='.$database,
            'DB_HOST='.$this->databaseHost($database),
            'DB_PORT='.$this->databasePort($database),
            'DB_DATABASE=laravel',
            'DB_USERNAME=sail',
            'DB_PASSWORD=password',
            '',
            'CACHE_STORE='.$this->builderValue($loadout, 'runtime.cache', 'database'),
            'QUEUE_CONNECTION='.($this->foundation($loadout, 'queue_monitoring') === 'horizon' ? 'redis' : $this->builderValue($loadout, 'runtime.queue', 'database')),
            'SESSION_DRIVER=database',
            '',
            'MAIL_MAILER='.$this->mailMailer($mail),
            'MAIL_HOST='.$this->mailHost($mail),
            'MAIL_PORT='.$this->mailPort($mail),
            'SCOUT_DRIVER='.$this->searchDriver($loadout),
            ...$this->reverbEnvironment($loadout),
        ])."\n";
    }

    /**
     * @param  Collection<int, LoadoutPackage>  $packages
     */
    private function readme(Loadout $loadout, Collection $packages): string
    {
        $packageLines = $packages
            ->map(fn (LoadoutPackage $package): string => $this->packageReadmeLine($package))
            ->implode("\n");

        return implode("\n", [
            '# '.$loadout->title,
            '',
            $loadout->description ?: 'Generated with Laravel Loadout.',
            '',
            'Shareable template URL: '.$this->shareableTemplateUrl($loadout),
            '',
            '## Stack',
            '',
            '- Laravel '.$this->builderValue($loadout, 'project.laravel_version', '13.x'),
            '- PHP '.$this->builderValue($loadout, 'project.php_version', '8.4'),
            '- Frontend: '.$this->label($this->builderValue($loadout, 'starter.frontend_stack', 'react-inertia')),
            '- Authentication: '.$this->label($this->builderValue($loadout, 'starter.authentication', 'laravel-auth')),
            '- Runtime: '.$this->label($this->builderValue($loadout, 'runtime.local_runtime', 'sail')),
            '- Database: '.$this->label($this->builderValue($loadout, 'runtime.database', 'mysql')),
            '- Cache: '.$this->label($this->builderValue($loadout, 'runtime.cache', 'database')),
            '- Queue: '.$this->label($this->builderValue($loadout, 'runtime.queue', 'database')),
            '- Package manager: '.$this->packageManagerLabel($this->packageManager($loadout)),
            ...$this->foundationReadmeLines($loadout),
            '',
            '## Selected Packages',
            '',
            $packageLines !== '' ? $packageLines : '- None selected yet',
            '',
            '## Setup',
            '',
            '```bash',
            ...$this->setupCommands($loadout),
            '```',
            ...($this->foundation($loadout, 'broadcasting') === 'reverb'
                ? ['', 'Import `resources/js/echo.ts` from your frontend bootstrap entry before subscribing to channels.']
                : []),
        ])."\n";
    }

    /**
     * @return array<int, string>
     */
    private function setupCommands(Loadout $loadout): array
    {
        $packageManager = $this->packageManager($loadout);

        $usesSail = $this->builderValue($loadout, 'runtime.local_runtime', 'sail') === 'sail';
        $commands = ! $usesSail
            ? [
                'cp .env.loadout .env',
                'composer install',
                $packageManager.' install',
                'php artisan key:generate',
            ]
            : [
                'cp .env.loadout .env',
                'docker run --rm \\',
                '    -u "$(id -u):$(id -g)" \\',
                '    -v "$(pwd):/var/www/html" \\',
                '    -w /var/www/html \\',
                '    laravelsail/php'.str_replace('.', '', $this->sailPhpVersion($loadout)).'-composer:latest \\',
                '    composer install --ignore-platform-reqs',
                './vendor/bin/sail up -d',
                './vendor/bin/sail '.$packageManager.' install',
                './vendor/bin/sail artisan key:generate',
            ];

        $artisan = $usesSail ? './vendor/bin/sail artisan ' : 'php artisan ';

        foreach ($this->foundationInstallCommands($loadout) as $command) {
            $commands[] = $artisan.$command;
        }

        $commands[] = $artisan.'migrate';

        return $commands;
    }

    /**
     * @return array<int, string>
     */
    private function foundationComposerPackages(Loadout $loadout): array
    {
        return array_values(array_filter([
            $this->foundation($loadout, 'api_auth') === 'sanctum' ? 'laravel/sanctum' : null,
            $this->foundation($loadout, 'broadcasting') === 'reverb' ? 'laravel/reverb' : null,
            $this->foundation($loadout, 'search') === 'scout' ? 'laravel/scout' : null,
            $this->foundation($loadout, 'queue_monitoring') === 'horizon' ? 'laravel/horizon' : null,
            $this->foundation($loadout, 'observability') === 'telescope' ? 'laravel/telescope' : null,
            $this->foundation($loadout, 'observability') === 'pulse' ? 'laravel/pulse' : null,
        ]));
    }

    /**
     * @return array<int, string>
     */
    private function foundationInstallCommands(Loadout $loadout): array
    {
        return array_values(array_filter([
            $this->foundation($loadout, 'api_auth') === 'sanctum' ? 'install:api' : null,
            $this->foundation($loadout, 'broadcasting') === 'reverb' ? 'reverb:install' : null,
            $this->foundation($loadout, 'search') === 'scout' ? 'scout:install' : null,
            $this->foundation($loadout, 'queue_monitoring') === 'horizon' ? 'horizon:install' : null,
            $this->foundation($loadout, 'observability') === 'telescope' ? 'telescope:install' : null,
            $this->foundation($loadout, 'observability') === 'pulse' ? 'pulse:install' : null,
        ]));
    }

    /**
     * @return array<int, string>
     */
    private function foundationReadmeLines(Loadout $loadout): array
    {
        return array_map(
            fn (string $package): string => '- Foundation: '.$this->label(str($package)->after('/')->toString()),
            $this->foundationComposerPackages($loadout),
        );
    }

    /**
     * @return array<int, string>
     */
    private function reverbEnvironment(Loadout $loadout): array
    {
        if ($this->foundation($loadout, 'broadcasting') !== 'reverb') {
            return [];
        }

        return [
            '',
            'BROADCAST_CONNECTION=reverb',
            'REVERB_APP_ID=loadout',
            'REVERB_APP_KEY=loadout-key',
            'REVERB_APP_SECRET=loadout-secret',
            'REVERB_HOST=localhost',
            'REVERB_PORT=8080',
            'REVERB_SCHEME=http',
            'VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"',
            'VITE_REVERB_HOST="${REVERB_HOST}"',
            'VITE_REVERB_PORT="${REVERB_PORT}"',
            'VITE_REVERB_SCHEME="${REVERB_SCHEME}"',
        ];
    }

    private function echoConfiguration(): string
    {
        return <<<'TYPESCRIPT'
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
TYPESCRIPT;
    }

    private function foundation(Loadout $loadout, string $key): string
    {
        return $this->builderValue($loadout, 'starter.'.$key, 'none');
    }

    private function shareableTemplateUrl(Loadout $loadout): string
    {
        if ($loadout->visibility === LoadoutVisibility::Private) {
            return 'Publish this loadout in Laravel Loadout to generate a public URL.';
        }

        return route('loadouts.show', $loadout);
    }

    private function builderValue(Loadout $loadout, string $key, string $default): string
    {
        $value = data_get($loadout->builder_state, $key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function packageManager(Loadout $loadout): string
    {
        $packageManager = $this->builderValue($loadout, 'project.package_manager', 'yarn');

        return in_array($packageManager, ['npm', 'pnpm', 'bun', 'yarn'], true)
            ? $packageManager
            : 'yarn';
    }

    private function composerPackageName(Loadout $loadout): string
    {
        return 'laravel-loadout/'.$loadout->projectSlug();
    }

    private function sailPhpVersion(Loadout $loadout): string
    {
        $phpVersion = $this->builderValue($loadout, 'project.php_version', '8.4');

        return in_array($phpVersion, ['8.4', '8.5'], true) ? $phpVersion : '8.4';
    }

    private function packageManagerSpecification(string $packageManager): string
    {
        return match ($packageManager) {
            'npm' => 'npm@11.0.0',
            'pnpm' => 'pnpm@10.0.0',
            'bun' => 'bun@1.2.0',
            default => 'yarn@1.22.22',
        };
    }

    private function packageManagerLabel(string $packageManager): string
    {
        return match ($packageManager) {
            'npm' => 'npm',
            'pnpm' => 'pnpm',
            'bun' => 'Bun',
            default => 'Yarn',
        };
    }

    private function isDevDependency(LoadoutPackage $package): bool
    {
        $options = $package->install_options;

        return is_array($options) && ($options['dev_dependency'] ?? false) === true;
    }

    private function packageReadmeLine(LoadoutPackage $package): string
    {
        $line = '- '.($package->composer_name ?? $package->display_name);

        if ($this->isDevDependency($package)) {
            $line .= ' (dev dependency)';
        }

        if ($package->notes) {
            $line .= ' - '.$package->notes;
        }

        return $line;
    }

    private function databaseHost(string $database): string
    {
        return match ($database) {
            'pgsql' => 'pgsql',
            'sqlite' => 'database/database.sqlite',
            default => 'mysql',
        };
    }

    private function databasePort(string $database): string
    {
        return match ($database) {
            'pgsql' => '5432',
            'sqlite' => '',
            default => '3306',
        };
    }

    private function mailMailer(string $mail): string
    {
        return $mail === 'smtp' ? 'smtp' : $mail;
    }

    private function mailHost(string $mail): string
    {
        return match ($mail) {
            'mailpit' => 'mailpit',
            'smtp' => 'smtp.example.com',
            default => '',
        };
    }

    private function mailPort(string $mail): string
    {
        return match ($mail) {
            'mailpit' => '1025',
            'smtp' => '587',
            default => '',
        };
    }

    private function searchDriver(Loadout $loadout): string
    {
        return $this->builderValue($loadout, 'runtime.search', 'none') === 'meilisearch' ? 'meilisearch' : 'collection';
    }

    private function label(string $value): string
    {
        return str($value)->replace('-', ' ')->headline()->toString();
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function prettyJson(array $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }
}
