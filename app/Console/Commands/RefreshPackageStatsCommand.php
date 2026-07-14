<?php

namespace App\Console\Commands;

use App\Jobs\RefreshPackageStats;
use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class RefreshPackageStatsCommand extends Command
{
    protected $signature = 'packages:refresh-stats
        {package? : Composer package name or package id to refresh}
        {--all : Refresh every curated package instead of only stale packages}';

    protected $description = 'Refresh stored package statistics from Packagist and GitHub.';

    public function handle(RefreshPackageStats $job): int
    {
        $query = Package::query()
            ->curated()
            ->orderBy('id');

        $package = $this->argument('package');

        if (is_string($package) && $package !== '') {
            $query = $this->applyPackageFilter($query, $package);
        } elseif (! $this->option('all')) {
            $query->where(function (Builder $query): void {
                $query
                    ->whereNull('stats_refreshed_at')
                    ->orWhere('stats_refreshed_at', '<=', now()->subHour());
            });
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->components->info('No packages need a stats refresh.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $refreshed = 0;

        $query->chunkById(50, function ($packages) use ($job, $bar, &$refreshed): void {
            foreach ($packages as $package) {
                $job->refreshPackage($package);
                $refreshed++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->components->info("Refreshed stats for {$refreshed} packages.");

        return self::SUCCESS;
    }

    /**
     * @param  Builder<Package>  $query
     * @return Builder<Package>
     */
    private function applyPackageFilter(Builder $query, string $package): Builder
    {
        if (ctype_digit($package)) {
            return $query->whereKey((int) $package);
        }

        return $query->where('composer_name', $package);
    }
}
