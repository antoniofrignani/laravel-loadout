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
            [
                'category' => 'ai-and-automation',
                'name' => 'USAIGE',
                'composer_name' => 'laraveljutsu/usaige',
                'description' => 'Track Laravel AI SDK interactions with token usage, costs, and provider metadata.',
                'tags' => ['ai', 'observability', 'token-usage', 'costs', 'laravel-ai'],
                'source_url' => 'https://github.com/ludoguenet/usaige',
                'github_url' => 'https://github.com/ludoguenet/usaige',
                'docs_url' => 'https://github.com/ludoguenet/usaige#readme',
            ],
            [
                'category' => 'billing-and-payments',
                'name' => 'Laravel MPP',
                'composer_name' => 'square1/laravel-mpp',
                'description' => 'Charge AI agents per request with the Machine Payments Protocol from Laravel routes.',
                'tags' => ['payments', 'ai-agents', 'stripe', 'api', 'mpp'],
                'source_url' => 'https://github.com/square1-io/laravel-mpp',
                'github_url' => 'https://github.com/square1-io/laravel-mpp',
                'docs_url' => 'https://github.com/square1-io/laravel-mpp#readme',
            ],
            [
                'category' => 'ai-and-automation',
                'name' => 'Laravel AIGEO',
                'composer_name' => 'hszope/laravel-aigeo',
                'description' => 'Optimize Laravel product data for visibility in AI-generated search answers.',
                'tags' => ['ai', 'seo', 'geo', 'json-ld', 'ecommerce'],
                'source_url' => 'https://github.com/GitHiteshZope/aigeo',
                'github_url' => 'https://github.com/GitHiteshZope/aigeo',
                'docs_url' => 'https://github.com/GitHiteshZope/aigeo#readme',
            ],
            [
                'category' => 'observability-and-debugging',
                'name' => 'Laravel Time Machine',
                'composer_name' => 'jaydeep/laravel-time-machine',
                'description' => 'Profile and visualize each stage of the Laravel request lifecycle.',
                'tags' => ['profiling', 'debugging', 'performance', 'timeline', 'diagnostics'],
                'source_url' => 'https://github.com/JaydeepGadhiya/laravel-timemachine',
                'github_url' => 'https://github.com/JaydeepGadhiya/laravel-timemachine',
                'docs_url' => 'https://github.com/JaydeepGadhiya/laravel-timemachine#readme',
            ],
            [
                'category' => 'data-and-content',
                'name' => 'Queue SQL',
                'composer_name' => 'kamranata/queue-sql',
                'description' => 'Queue Laravel write queries across parallel batched jobs.',
                'tags' => ['queues', 'database', 'eloquent', 'batching', 'bulk-operations'],
                'source_url' => 'https://github.com/kamranata/queue-sql',
                'github_url' => 'https://github.com/kamranata/queue-sql',
                'docs_url' => 'https://github.com/kamranata/queue-sql#readme',
            ],
            [
                'category' => 'developer-experience',
                'name' => 'Laravel Cooldown',
                'composer_name' => 'zaber-dev/laravel-cooldown',
                'description' => 'Manage application action cooldowns with middleware, Eloquent, and cache-backed storage.',
                'tags' => ['cooldown', 'middleware', 'cache', 'eloquent', 'redis'],
                'source_url' => 'https://github.com/zaber-dev/laravel-cooldown',
                'github_url' => 'https://github.com/zaber-dev/laravel-cooldown',
                'docs_url' => 'https://github.com/zaber-dev/laravel-cooldown#readme',
            ],
            [
                'category' => 'developer-experience',
                'name' => 'Laravel Quota',
                'composer_name' => 'zaber-dev/laravel-quota',
                'description' => 'Manage application quotas with calendar periods and pluggable storage backends.',
                'tags' => ['quotas', 'usage-limits', 'middleware', 'cache', 'redis'],
                'source_url' => 'https://github.com/zaber-dev/laravel-quota',
                'github_url' => 'https://github.com/zaber-dev/laravel-quota',
                'docs_url' => 'https://github.com/zaber-dev/laravel-quota#readme',
            ],
            [
                'category' => 'observability-and-debugging',
                'name' => 'Yammi Audit Log',
                'composer_name' => 'romalytar/yammi-audit-log-laravel',
                'description' => 'Track Eloquent model changes with actor attribution, field diffs, and a timeline dashboard.',
                'tags' => ['audit-log', 'activity-log', 'eloquent', 'compliance', 'dashboard'],
                'source_url' => 'https://github.com/RomaLytar/yammi-audit-Log',
                'github_url' => 'https://github.com/RomaLytar/yammi-audit-Log',
                'docs_url' => 'https://github.com/RomaLytar/yammi-audit-Log#readme',
            ],
            [
                'category' => 'observability-and-debugging',
                'name' => 'Laravel Vigilance',
                'composer_name' => 'anousss007/vigilance',
                'description' => 'Monitor Laravel queues, jobs, commands, and scheduled tasks from one control center.',
                'tags' => ['monitoring', 'queues', 'scheduler', 'dashboard', 'observability'],
                'source_url' => 'https://github.com/anousss007/laravel-vigilance',
                'github_url' => 'https://github.com/anousss007/laravel-vigilance',
                'docs_url' => 'https://github.com/anousss007/laravel-vigilance#readme',
            ],
            [
                'category' => 'observability-and-debugging',
                'name' => 'Scheduler List',
                'composer_name' => 'devakshay/scheduler-list-laravel',
                'description' => 'Monitor, search, and manually run scheduled tasks from a real-time Laravel dashboard.',
                'tags' => ['scheduler', 'cron', 'dashboard', 'monitoring', 'developer-tools'],
                'source_url' => 'https://github.com/Akshayp2002/scheduler-list-laravel',
                'github_url' => 'https://github.com/Akshayp2002/scheduler-list-laravel',
                'docs_url' => 'https://github.com/Akshayp2002/scheduler-list-laravel#readme',
            ],
            [
                'category' => 'data-and-content',
                'name' => 'Laravel Reorderable',
                'composer_name' => 'atomcoder/laravel-reorderable',
                'description' => 'Add drag-and-drop ordering to Laravel Eloquent models.',
                'tags' => ['eloquent', 'sorting', 'drag-and-drop', 'livewire', 'data'],
                'source_url' => 'https://github.com/RichieMcMullen/laravel-reorderable',
                'github_url' => 'https://github.com/RichieMcMullen/laravel-reorderable',
                'docs_url' => 'https://github.com/RichieMcMullen/laravel-reorderable#readme',
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
                    'php_version_constraints' => ['^8.4'],
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
