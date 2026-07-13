<?php

namespace App\Services;

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

        return [
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
                'content' => $this->composeYaml($packages),
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

        foreach ($packages as $package) {
            if ($package->composer_name) {
                $require[$package->composer_name] = '*';
            }
        }

        return $this->prettyJson([
            'name' => 'laravel-loadout/'.str($loadout->slug)->slug()->toString(),
            'type' => 'project',
            'description' => $loadout->description ?: 'Generated Laravel Loadout starter.',
            'require' => $require,
            'require-dev' => [
                'laravel/pint' => '^1.25',
                'phpunit/phpunit' => '^12.0',
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                    'Database\\Factories\\' => 'database/factories/',
                    'Database\\Seeders\\' => 'database/seeders/',
                ],
            ],
            'scripts' => [
                'post-autoload-dump' => [
                    'Illuminate\\Foundation\\ComposerScripts::postAutoloadDump',
                    '@php artisan package:discover --ansi',
                ],
                'test' => '@php artisan test',
            ],
        ]);
    }

    private function packageJson(Loadout $loadout): string
    {
        return $this->prettyJson([
            'private' => true,
            'type' => 'module',
            'scripts' => [
                'build' => 'vite build',
                'dev' => 'vite --host 0.0.0.0',
            ],
            'dependencies' => [
                '@inertiajs/react' => '^3.0.0',
                '@vitejs/plugin-react' => '^5.0.0',
                'vite' => '^8.0.0',
                'typescript' => '^5.9.0',
            ],
            'devDependencies' => [
                'tailwindcss' => '^4.0.0',
            ],
            'packageManager' => $this->builderValue($loadout, 'project.package_manager', 'yarn@1.22.22'),
        ]);
    }

    /**
     * @param  Collection<int, LoadoutPackage>  $packages
     */
    private function composeYaml(Collection $packages): string
    {
        $services = $this->requiredServices($packages);

        $yaml = [
            'services:',
            '  laravel.test:',
            '    build:',
            '      context: ./vendor/laravel/sail/runtimes/8.4',
            '      dockerfile: Dockerfile',
            '    ports:',
            '      - "${APP_PORT:-80}:80"',
            '    environment:',
            '      LARAVEL_SAIL: 1',
            '    volumes:',
            '      - ".:/var/www/html"',
        ];

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

        return implode("\n", [
            'APP_NAME="'.$appName.'"',
            'APP_ENV=local',
            'APP_KEY=',
            'APP_DEBUG=true',
            'APP_URL=http://localhost',
            '',
            'DB_CONNECTION=mysql',
            'DB_HOST=mysql',
            'DB_PORT=3306',
            'DB_DATABASE=laravel',
            'DB_USERNAME=sail',
            'DB_PASSWORD=password',
            '',
            'CACHE_STORE=database',
            'QUEUE_CONNECTION=database',
            'SESSION_DRIVER=database',
            '',
            'MAIL_MAILER=log',
        ])."\n";
    }

    /**
     * @param  Collection<int, LoadoutPackage>  $packages
     */
    private function readme(Loadout $loadout, Collection $packages): string
    {
        $packageLines = $packages
            ->map(fn (LoadoutPackage $package): string => '- '.($package->composer_name ?? $package->display_name))
            ->implode("\n");

        return implode("\n", [
            '# '.$loadout->title,
            '',
            $loadout->description ?: 'Generated with Laravel Loadout.',
            '',
            'Shareable template URL: '.route('builder.review'),
            '',
            '## Stack',
            '',
            '- Laravel '.$this->builderValue($loadout, 'project.laravel_version', '13.x'),
            '- PHP '.$this->builderValue($loadout, 'project.php_version', '8.4'),
            '- Yarn package manager',
            '',
            '## Selected Packages',
            '',
            $packageLines !== '' ? $packageLines : '- None selected yet',
            '',
            '## Setup',
            '',
            '```bash',
            'cp .env.loadout .env',
            'composer install',
            'yarn install',
            './vendor/bin/sail up -d',
            './vendor/bin/sail artisan key:generate',
            './vendor/bin/sail artisan migrate',
            '```',
        ])."\n";
    }

    private function builderValue(Loadout $loadout, string $key, string $default): string
    {
        $value = data_get($loadout->builder_state, $key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function prettyJson(array $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }
}
