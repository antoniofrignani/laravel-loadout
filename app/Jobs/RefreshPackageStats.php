<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\PackageStatSnapshot;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshPackageStats implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function handle(): void
    {
        Package::query()
            ->curated()
            ->orderBy('id')
            ->chunkById(50, function ($packages): void {
                foreach ($packages as $package) {
                    $this->refreshPackage($package);
                }
            });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Package stats refresh job failed.', [
            'error' => $exception?->getMessage(),
        ]);
    }

    public function uniqueId(): string
    {
        return 'package-stats-refresh';
    }

    private function refreshPackage(Package $package): void
    {
        $refreshedAt = now();
        $updates = [];

        if ($package->composer_name) {
            $updates = array_merge($updates, $this->refreshPackagist($package, $refreshedAt));
        }

        if ($package->github_url) {
            $updates = array_merge($updates, $this->refreshGithub($package, $refreshedAt));
        }

        if ($updates !== []) {
            $updates['stats_refreshed_at'] = $refreshedAt;
            $package->forceFill($updates)->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshPackagist(Package $package, DateTimeInterface $refreshedAt): array
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(3)
                ->retry([100, 300])
                ->acceptJson()
                ->get("https://packagist.org/packages/{$package->composer_name}.json")
                ->throw();
        } catch (Throwable $exception) {
            $this->recordFailure($package, 'packagist', $refreshedAt, $exception);

            return [];
        }

        $stats = $this->packagistStats($response);

        PackageStatSnapshot::query()->create([
            'package_id' => $package->id,
            'source' => 'packagist',
            'stars' => $stats['stars'],
            'downloads' => $stats['downloads'],
            'last_release_at' => $stats['last_release_at'],
            'refreshed_at' => $refreshedAt,
            'metadata' => [
                'name' => $response->json('package.name'),
                'repository' => $response->json('package.repository'),
            ],
        ]);

        return array_filter([
            'stars' => $stats['stars'],
            'downloads' => $stats['downloads'],
            'last_release_at' => $stats['last_release_at'],
        ], fn ($value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshGithub(Package $package, DateTimeInterface $refreshedAt): array
    {
        $repository = $this->githubRepository($package->github_url);

        if (! $repository) {
            return [];
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(3)
                ->retry([100, 300])
                ->acceptJson()
                ->withUserAgent('Laravel Loadout')
                ->get("https://api.github.com/repos/{$repository}")
                ->throw();
        } catch (Throwable $exception) {
            $this->recordFailure($package, 'github', $refreshedAt, $exception);

            return [];
        }

        $stats = [
            'stars' => $response->json('stargazers_count'),
            'last_source_update_at' => $this->parseDate($response->json('pushed_at') ?? $response->json('updated_at')),
        ];

        PackageStatSnapshot::query()->create([
            'package_id' => $package->id,
            'source' => 'github',
            'stars' => $stats['stars'],
            'last_source_update_at' => $stats['last_source_update_at'],
            'refreshed_at' => $refreshedAt,
            'metadata' => [
                'full_name' => $response->json('full_name'),
                'html_url' => $response->json('html_url'),
            ],
        ]);

        return array_filter($stats, fn ($value): bool => $value !== null);
    }

    /**
     * @return array{stars: int|null, downloads: int|null, last_release_at: DateTimeInterface|null}
     */
    private function packagistStats(Response $response): array
    {
        $versions = $response->json('package.versions', []);
        $releaseDates = collect(is_array($versions) ? $versions : [])
            ->pluck('time')
            ->filter()
            ->map(fn (string $time): Carbon => Carbon::parse($time));

        return [
            'stars' => $response->json('package.favers'),
            'downloads' => $response->json('package.downloads.total'),
            'last_release_at' => $releaseDates->max(),
        ];
    }

    private function githubRepository(?string $githubUrl): ?string
    {
        if (! $githubUrl) {
            return null;
        }

        $host = parse_url($githubUrl, PHP_URL_HOST);
        $path = trim((string) parse_url($githubUrl, PHP_URL_PATH), '/');

        if (! is_string($host) || ! str_contains($host, 'github.com') || $path === '') {
            return null;
        }

        $segments = explode('/', $path);

        if (count($segments) < 2) {
            return null;
        }

        return $segments[0].'/'.$segments[1];
    }

    private function parseDate(mixed $value): ?DateTimeInterface
    {
        return is_string($value) && $value !== '' ? Carbon::parse($value) : null;
    }

    private function recordFailure(Package $package, string $source, DateTimeInterface $refreshedAt, Throwable $exception): void
    {
        PackageStatSnapshot::query()->create([
            'package_id' => $package->id,
            'source' => $source,
            'refreshed_at' => $refreshedAt,
            'error_message' => $exception->getMessage(),
            'metadata' => [
                'exception' => $exception::class,
            ],
        ]);
    }
}
