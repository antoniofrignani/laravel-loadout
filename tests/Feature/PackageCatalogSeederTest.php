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
}
