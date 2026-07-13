<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\PackageCatalog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PackageCatalogQueryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_searches_curated_packages_by_name_description_and_package_name(): void
    {
        $category = PackageCategory::factory()->create(['slug' => 'developer-experience']);

        Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
            'description' => 'Local request inspection toolbar',
        ]);
        Package::factory()->for($category, 'category')->create([
            'name' => 'Media Library',
            'composer_name' => 'spatie/laravel-medialibrary',
            'description' => 'File attachments',
        ]);

        $results = app(PackageCatalog::class)->search(['search' => 'debugbar']);

        $this->assertCount(1, $results->items());
        $this->assertSame('barryvdh/laravel-debugbar', $results->items()[0]->composer_name);
    }

    public function test_it_filters_by_category_service_and_laravel_version(): void
    {
        $search = PackageCategory::factory()->create(['slug' => 'search']);
        $media = PackageCategory::factory()->create(['slug' => 'media-and-uploads']);

        Package::factory()->for($search, 'category')->create([
            'name' => 'Meilisearch PHP',
            'composer_name' => 'meilisearch/meilisearch-php',
            'required_services' => ['meilisearch'],
            'laravel_version_constraints' => ['^13.0'],
        ]);
        Package::factory()->for($media, 'category')->create([
            'name' => 'Media Library',
            'composer_name' => 'spatie/laravel-medialibrary',
            'required_services' => [],
            'laravel_version_constraints' => ['^12.0'],
        ]);

        $results = app(PackageCatalog::class)->search([
            'category' => 'search',
            'service' => 'meilisearch',
            'laravel_version' => '^13.0',
        ]);

        $this->assertCount(1, $results->items());
        $this->assertSame('meilisearch/meilisearch-php', $results->items()[0]->composer_name);
    }

    public function test_it_filters_official_and_community_packages(): void
    {
        $category = PackageCategory::factory()->create();

        Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Horizon',
            'composer_name' => 'laravel/horizon',
            'is_official' => true,
        ]);
        Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Permission',
            'composer_name' => 'spatie/laravel-permission',
            'is_official' => false,
        ]);

        $official = app(PackageCatalog::class)->search(['type' => 'official']);
        $community = app(PackageCatalog::class)->search(['type' => 'community']);

        $this->assertSame('laravel/horizon', $official->items()[0]->composer_name);
        $this->assertSame('spatie/laravel-permission', $community->items()[0]->composer_name);
    }

    public function test_it_sorts_by_stars_downloads_last_update_and_name(): void
    {
        $category = PackageCategory::factory()->create();

        Package::factory()->for($category, 'category')->create([
            'name' => 'Bravo',
            'composer_name' => 'vendor/bravo',
            'stars' => 10,
            'downloads' => 200,
            'last_source_update_at' => now()->subDays(2),
        ]);
        Package::factory()->for($category, 'category')->create([
            'name' => 'Alpha',
            'composer_name' => 'vendor/alpha',
            'stars' => 20,
            'downloads' => 100,
            'last_source_update_at' => now()->subDay(),
        ]);

        $byStars = app(PackageCatalog::class)->search(['sort' => 'stars']);
        $byDownloads = app(PackageCatalog::class)->search(['sort' => 'downloads']);
        $byLastUpdate = app(PackageCatalog::class)->search(['sort' => 'last_update']);
        $byName = app(PackageCatalog::class)->search(['sort' => 'name']);

        $this->assertSame('vendor/alpha', $byStars->items()[0]->composer_name);
        $this->assertSame('vendor/bravo', $byDownloads->items()[0]->composer_name);
        $this->assertSame('vendor/alpha', $byLastUpdate->items()[0]->composer_name);
        $this->assertSame('vendor/alpha', $byName->items()[0]->composer_name);
    }

    public function test_it_limits_per_page_to_a_safe_range(): void
    {
        Package::factory()->count(3)->create();

        $tooSmall = app(PackageCatalog::class)->search(['per_page' => 0]);
        $tooLarge = app(PackageCatalog::class)->search(['per_page' => 500]);

        $this->assertSame(1, $tooSmall->perPage());
        $this->assertSame(100, $tooLarge->perPage());
    }
}
