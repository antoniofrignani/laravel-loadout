<?php

namespace Tests\Feature;

use App\Jobs\RefreshPackageStats;
use App\Models\Package;
use App\Models\PackageStatSnapshot;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RefreshPackageStatsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_refresh_package_stats_stores_snapshots_and_updates_package_totals(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'packagist.org/packages/vendor/custom-tool.json' => Http::response([
                'package' => [
                    'name' => 'vendor/custom-tool',
                    'repository' => 'https://github.com/vendor/custom-tool',
                    'favers' => 432,
                    'downloads' => [
                        'total' => 120000,
                    ],
                    'versions' => [
                        'v1.0.0' => ['time' => '2026-01-02T03:04:05+00:00'],
                        'v1.1.0' => ['time' => '2026-05-06T07:08:09+00:00'],
                    ],
                ],
            ]),
            'api.github.com/repos/vendor/custom-tool' => Http::response([
                'full_name' => 'vendor/custom-tool',
                'html_url' => 'https://github.com/vendor/custom-tool',
                'stargazers_count' => 987,
                'pushed_at' => '2026-06-07T08:09:10Z',
            ]),
        ]);

        $package = Package::factory()->create([
            'composer_name' => 'vendor/custom-tool',
            'github_url' => 'https://github.com/vendor/custom-tool',
            'stars' => 0,
            'downloads' => 0,
            'last_release_at' => null,
            'last_source_update_at' => null,
            'stats_refreshed_at' => null,
        ]);

        (new RefreshPackageStats)->handle();

        $package->refresh();

        $this->assertSame(987, $package->stars);
        $this->assertSame(120000, $package->downloads);
        $this->assertSame('2026-05-06 07:08:09', $package->last_release_at?->toDateTimeString());
        $this->assertSame('2026-06-07 08:09:10', $package->last_source_update_at?->toDateTimeString());
        $this->assertNotNull($package->stats_refreshed_at);
        $this->assertSame(2, PackageStatSnapshot::query()->whereBelongsTo($package)->count());
    }

    public function test_refresh_package_stats_records_failed_source_attempts(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'packagist.org/packages/vendor/custom-tool.json' => Http::response([], 500),
            'api.github.com/repos/vendor/custom-tool' => Http::response([], 404),
        ]);

        $package = Package::factory()->create([
            'composer_name' => 'vendor/custom-tool',
            'github_url' => 'https://github.com/vendor/custom-tool',
            'stats_refreshed_at' => null,
        ]);

        (new RefreshPackageStats)->handle();

        $package->refresh();

        $this->assertNull($package->stats_refreshed_at);
        $this->assertSame(2, PackageStatSnapshot::query()->whereBelongsTo($package)->whereNotNull('error_message')->count());
    }

    public function test_refresh_package_stats_command_can_target_one_package(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'packagist.org/packages/vendor/custom-tool.json' => Http::response([
                'package' => [
                    'name' => 'vendor/custom-tool',
                    'repository' => 'https://github.com/vendor/custom-tool',
                    'favers' => 123,
                    'downloads' => [
                        'total' => 4567,
                    ],
                    'versions' => [
                        'v1.0.0' => ['time' => '2026-01-02T03:04:05+00:00'],
                    ],
                ],
            ]),
            'api.github.com/repos/vendor/custom-tool' => Http::response([
                'full_name' => 'vendor/custom-tool',
                'html_url' => 'https://github.com/vendor/custom-tool',
                'stargazers_count' => 321,
                'pushed_at' => '2026-06-07T08:09:10Z',
            ]),
        ]);

        $package = Package::factory()->create([
            'composer_name' => 'vendor/custom-tool',
            'github_url' => 'https://github.com/vendor/custom-tool',
            'stats_refreshed_at' => null,
        ]);

        Package::factory()->create([
            'composer_name' => 'vendor/other-tool',
            'github_url' => 'https://github.com/vendor/other-tool',
            'stats_refreshed_at' => null,
        ]);

        $this->artisan('packages:refresh-stats', [
            'package' => 'vendor/custom-tool',
        ])->assertSuccessful();

        $package->refresh();

        $this->assertSame(321, $package->stars);
        $this->assertSame(4567, $package->downloads);
        $this->assertSame(2, PackageStatSnapshot::query()->whereBelongsTo($package)->count());
    }

    public function test_refresh_package_stats_command_skips_when_nothing_is_stale(): void
    {
        Http::preventStrayRequests();

        Package::factory()->create([
            'composer_name' => 'vendor/current-tool',
            'github_url' => 'https://github.com/vendor/current-tool',
            'stats_refreshed_at' => now(),
        ]);

        $this->artisan('packages:refresh-stats')
            ->expectsOutputToContain('No packages need a stats refresh.')
            ->assertSuccessful();

        $this->assertSame(0, PackageStatSnapshot::query()->count());
    }
}
