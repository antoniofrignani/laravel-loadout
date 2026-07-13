<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BuilderPackageCatalogPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_builder_package_catalog_page_is_displayed(): void
    {
        $this->withoutVite();

        $category = PackageCategory::factory()->create([
            'name' => 'Developer experience',
            'slug' => 'developer-experience',
        ]);
        Package::factory()->for($category, 'category')->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
            'description' => 'Local request inspection toolbar',
            'stars' => 12000,
        ]);

        $this->get(route('builder.packages'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/packages')
                ->has('categories', 1)
                ->where('categories.0.slug', 'developer-experience')
                ->has('packages.data', 1)
                ->where('packages.data.0.composer_name', 'barryvdh/laravel-debugbar')
                ->where('packages.data.0.category.slug', 'developer-experience'),
            );
    }

    public function test_builder_package_catalog_page_respects_filters(): void
    {
        $this->withoutVite();

        $developerExperience = PackageCategory::factory()->create([
            'slug' => 'developer-experience',
        ]);
        $media = PackageCategory::factory()->create([
            'slug' => 'media-and-uploads',
        ]);

        Package::factory()->for($developerExperience, 'category')->create([
            'name' => 'Laravel Debugbar',
            'composer_name' => 'barryvdh/laravel-debugbar',
            'description' => 'Local request inspection toolbar',
            'required_services' => [],
        ]);
        Package::factory()->for($media, 'category')->create([
            'name' => 'Laravel Medialibrary',
            'composer_name' => 'spatie/laravel-medialibrary',
            'description' => 'File attachments',
            'required_services' => [],
        ]);

        $this->get(route('builder.packages', [
            'search' => 'media',
            'category' => 'media-and-uploads',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('builder/packages')
                ->where('filters.search', 'media')
                ->where('filters.category', 'media-and-uploads')
                ->has('packages.data', 1)
                ->where('packages.data.0.composer_name', 'spatie/laravel-medialibrary'),
            );
    }
}
