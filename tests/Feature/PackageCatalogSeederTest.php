<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageCategory;
use Database\Seeders\PackageCatalogSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PackageCatalogSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_seeds_laravel_news_community_packages(): void
    {
        $this->seed(PackageCatalogSeeder::class);

        $this->assertGreaterThanOrEqual(200, Package::query()->count());

        $package = Package::query()
            ->where('composer_name', 'dedoc/scramble')
            ->firstOrFail();

        $this->assertFalse($package->is_official);
        $this->assertFalse($package->is_featured);
        $this->assertSame('curated', $package->status->value);
        $this->assertSame('https://packagist.org/packages/dedoc/scramble', $package->packagist_url);
        $this->assertContains('laravel-news', $package->tags);

        $this->assertTrue(
            PackageCategory::query()
                ->where('slug', 'ai-and-automation')
                ->exists(),
        );
    }

    public function test_it_seeds_laravel_fuse_as_curated_package(): void
    {
        $this->seed(PackageCatalogSeeder::class);

        $package = Package::query()
            ->where('composer_name', 'harris21/laravel-fuse')
            ->firstOrFail();

        $this->assertSame('Laravel Fuse', $package->name);
        $this->assertTrue($package->is_featured);
        $this->assertSame('curated', $package->status->value);
        $this->assertSame('integrations', $package->category?->slug);
        $this->assertSame('https://github.com/harris21/laravel-fuse', $package->github_url);
        $this->assertContains('circuit-breaker', $package->tags);
        $this->assertSame(['php artisan vendor:publish --tag=fuse-config'], $package->publish_commands);
    }
}
