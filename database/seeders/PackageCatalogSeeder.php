<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Authentication and authorization', 'description' => 'Identity, roles, permissions, and access control.'],
            ['name' => 'Admin panels', 'description' => 'Back-office dashboards and application admin tooling.'],
            ['name' => 'Billing and payments', 'description' => 'Subscriptions, invoices, checkouts, and payments.'],
            ['name' => 'Media and uploads', 'description' => 'File uploads, media libraries, and image handling.'],
            ['name' => 'Search', 'description' => 'Application search and indexing packages.'],
            ['name' => 'Official Laravel', 'description' => 'First-party Laravel packages for common framework features.'],
            ['name' => 'Testing and quality', 'description' => 'Testing helpers, static analysis, and code quality tools.'],
            ['name' => 'Developer experience', 'description' => 'Productivity, debugging, and local development helpers.'],
            ['name' => 'Observability and debugging', 'description' => 'Runtime debugging, monitoring, and diagnostics.'],
            ['name' => 'AI and automation', 'description' => 'AI SDKs, agents, workflows, and automation helpers.'],
            ['name' => 'Integrations', 'description' => 'External APIs, notifications, webhooks, and service integrations.'],
            ['name' => 'Forms and validation', 'description' => 'Form builders, validation, schemas, and input handling.'],
            ['name' => 'Data and content', 'description' => 'Eloquent, database, content, and data modeling packages.'],
        ])->mapWithKeys(function (array $category, int $index): array {
            $model = PackageCategory::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            return [$model->slug => $model];
        });

        $packages = [
            [
                'category' => 'official-laravel',
                'name' => 'Laravel Sanctum',
                'composer_name' => 'laravel/sanctum',
                'description' => 'Official lightweight API token and SPA authentication package.',
                'tags' => ['official', 'api', 'authentication'],
                'is_official' => true,
                'migration_commands' => ['php artisan migrate'],
            ],
            [
                'category' => 'official-laravel',
                'name' => 'Laravel Horizon',
                'composer_name' => 'laravel/horizon',
                'description' => 'Official dashboard and monitoring for Redis queues.',
                'tags' => ['official', 'queues', 'redis'],
                'is_official' => true,
                'required_services' => ['redis'],
                'post_install_commands' => ['php artisan horizon:install'],
            ],
            [
                'category' => 'official-laravel',
                'name' => 'Laravel Scout',
                'composer_name' => 'laravel/scout',
                'description' => 'Official driver-based full-text search integration for Eloquent.',
                'tags' => ['official', 'search', 'meilisearch'],
                'is_official' => true,
                'required_services' => ['meilisearch'],
                'publish_commands' => ['php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"'],
            ],
            [
                'category' => 'official-laravel',
                'name' => 'Laravel Telescope',
                'composer_name' => 'laravel/telescope',
                'description' => 'Official local observability assistant for requests, exceptions, jobs, and database queries.',
                'tags' => ['official', 'debugging', 'observability'],
                'is_official' => true,
                'post_install_commands' => ['php artisan telescope:install'],
                'migration_commands' => ['php artisan migrate'],
            ],
            [
                'category' => 'authentication-and-authorization',
                'name' => 'Laravel Permission',
                'composer_name' => 'spatie/laravel-permission',
                'description' => 'Associate users with roles and permissions in a Laravel application.',
                'tags' => ['roles', 'permissions', 'authorization'],
                'publish_commands' => ['php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"'],
                'migration_commands' => ['php artisan migrate'],
            ],
            [
                'category' => 'media-and-uploads',
                'name' => 'Laravel Medialibrary',
                'composer_name' => 'spatie/laravel-medialibrary',
                'description' => 'Associate files with Eloquent models and manage conversions.',
                'tags' => ['uploads', 'media', 'images'],
                'publish_commands' => ['php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"'],
                'migration_commands' => ['php artisan migrate'],
            ],
            [
                'category' => 'developer-experience',
                'name' => 'Laravel Debugbar',
                'composer_name' => 'barryvdh/laravel-debugbar',
                'description' => 'Add a local debugging toolbar for requests, queries, views, and logs.',
                'tags' => ['debugging', 'queries', 'local-development'],
                'publish_commands' => ['php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"'],
            ],
            [
                'category' => 'testing-and-quality',
                'name' => 'Laravel IDE Helper',
                'composer_name' => 'barryvdh/laravel-ide-helper',
                'description' => 'Generate IDE helper files for better Laravel autocomplete.',
                'tags' => ['ide', 'autocomplete', 'developer-experience'],
                'post_install_commands' => ['php artisan ide-helper:generate'],
            ],
            [
                'category' => 'search',
                'name' => 'Meilisearch PHP',
                'composer_name' => 'meilisearch/meilisearch-php',
                'description' => 'Official PHP client for Meilisearch-powered search features.',
                'tags' => ['search', 'meilisearch', 'scout'],
                'required_services' => ['meilisearch'],
            ],
            [
                'category' => 'admin-panels',
                'name' => 'Filament',
                'composer_name' => 'filament/filament',
                'description' => 'Build Laravel admin panels, forms, tables, and dashboards.',
                'tags' => ['admin', 'dashboard', 'forms'],
                'post_install_commands' => ['php artisan filament:install --panels'],
            ],
            [
                'category' => 'integrations',
                'name' => 'Laravel Fuse',
                'composer_name' => 'harris21/laravel-fuse',
                'description' => 'Circuit breaker middleware for Laravel queue jobs that protects workers when external services fail.',
                'tags' => ['queues', 'circuit-breaker', 'resilience', 'external-services'],
                'source_url' => 'https://github.com/harris21/laravel-fuse',
                'github_url' => 'https://github.com/harris21/laravel-fuse',
                'docs_url' => 'https://github.com/harris21/laravel-fuse#readme',
                'publish_commands' => ['php artisan vendor:publish --tag=fuse-config'],
            ],
        ];

        $packages = array_merge($packages, $this->laravelNewsPackages());

        foreach ($packages as $package) {
            $composerName = $package['composer_name'];

            Package::query()->updateOrCreate(
                ['composer_name' => $composerName],
                [
                    'package_category_id' => $categories[$package['category']]->id,
                    'name' => $package['name'],
                    'slug' => Str::slug($composerName),
                    'description' => $package['description'],
                    'source_url' => $package['source_url'] ?? "https://github.com/{$composerName}",
                    'packagist_url' => "https://packagist.org/packages/{$composerName}",
                    'github_url' => $package['github_url'] ?? "https://github.com/{$composerName}",
                    'docs_url' => $package['docs_url'] ?? "https://github.com/{$composerName}#readme",
                    'install_command' => "composer require {$composerName}",
                    'post_install_commands' => $package['post_install_commands'] ?? [],
                    'publish_commands' => $package['publish_commands'] ?? [],
                    'migration_commands' => $package['migration_commands'] ?? [],
                    'required_services' => $package['required_services'] ?? [],
                    'laravel_version_constraints' => ['^13.0'],
                    'php_version_constraints' => ['^8.3'],
                    'tags' => $package['tags'],
                    'is_official' => $package['is_official'] ?? false,
                    'is_featured' => $package['is_featured'] ?? true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{
     *     category: string,
     *     name: string,
     *     composer_name: string,
     *     description: string,
     *     tags: array<int, string>,
     *     source_url?: string|null,
     *     github_url?: string|null,
     *     docs_url?: string|null,
     *     required_services?: array<int, string>,
     *     is_official?: bool,
     *     is_featured?: bool,
     * }>
     */
    private function laravelNewsPackages(): array
    {
        return require database_path('data/laravel-news-packages.php');
    }
}
