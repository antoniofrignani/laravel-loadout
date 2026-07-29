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

    public function test_it_seeds_the_requested_curated_packages(): void
    {
        $this->seed(PackageCatalogSeeder::class);

        $expectedPackages = [
            'laraveljutsu/usaige' => ['ai-and-automation', 'https://github.com/ludoguenet/usaige'],
            'square1/laravel-mpp' => ['billing-and-payments', 'https://github.com/square1-io/laravel-mpp'],
            'hszope/laravel-aigeo' => ['ai-and-automation', 'https://github.com/GitHiteshZope/aigeo'],
            'jaydeep/laravel-time-machine' => ['observability-and-debugging', 'https://github.com/JaydeepGadhiya/laravel-timemachine'],
            'kamranata/queue-sql' => ['data-and-content', 'https://github.com/kamranata/queue-sql'],
            'zaber-dev/laravel-cooldown' => ['developer-experience', 'https://github.com/zaber-dev/laravel-cooldown'],
            'zaber-dev/laravel-quota' => ['developer-experience', 'https://github.com/zaber-dev/laravel-quota'],
            'romalytar/yammi-audit-log-laravel' => ['observability-and-debugging', 'https://github.com/RomaLytar/yammi-audit-Log'],
            'anousss007/vigilance' => ['observability-and-debugging', 'https://github.com/anousss007/laravel-vigilance'],
            'devakshay/scheduler-list-laravel' => ['observability-and-debugging', 'https://github.com/Akshayp2002/scheduler-list-laravel'],
            'atomcoder/laravel-reorderable' => ['data-and-content', 'https://github.com/RichieMcMullen/laravel-reorderable'],
        ];

        $packages = Package::query()
            ->with('category')
            ->whereIn('composer_name', array_keys($expectedPackages))
            ->get()
            ->keyBy('composer_name');

        $this->assertCount(count($expectedPackages), $packages);

        foreach ($expectedPackages as $composerName => [$categorySlug, $githubUrl]) {
            $package = $packages->get($composerName);

            $this->assertNotNull($package);
            $this->assertTrue($package->is_featured);
            $this->assertSame('curated', $package->status->value);
            $this->assertSame($categorySlug, $package->category?->slug);
            $this->assertSame($githubUrl, $package->github_url);
            $this->assertSame("composer require {$composerName}", $package->install_command);
        }
    }
}
