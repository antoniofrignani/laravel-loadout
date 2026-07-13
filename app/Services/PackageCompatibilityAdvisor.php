<?php

namespace App\Services;

use App\Models\Loadout;
use App\Models\Package;

class PackageCompatibilityAdvisor
{
    /**
     * @return array<int, array{composer_name: string, title: string, reason: string}>
     */
    public function recommendations(Loadout $loadout): array
    {
        $recommendations = [];

        if ($this->builderValue($loadout, 'starter.frontend_stack', 'react-inertia') === 'api') {
            $recommendations[] = [
                'composer_name' => 'laravel/sanctum',
                'title' => 'Laravel Sanctum',
                'reason' => 'Recommended when the starter stack exposes first-party API authentication.',
            ];
        }

        if ($this->builderValue($loadout, 'runtime.queue', 'database') === 'redis') {
            $recommendations[] = [
                'composer_name' => 'laravel/horizon',
                'title' => 'Laravel Horizon',
                'reason' => 'Recommended for monitoring Redis-backed queues.',
            ];
        }

        if ($this->builderValue($loadout, 'runtime.search', 'none') === 'meilisearch') {
            $recommendations[] = [
                'composer_name' => 'laravel/scout',
                'title' => 'Laravel Scout',
                'reason' => 'Recommended for Laravel search indexing with Meilisearch.',
            ];
        }

        return $recommendations;
    }

    /**
     * @return array{name: string, description: string, tags: array<int, string>, required_services: array<int, string>, post_install_commands?: array<int, string>, publish_commands?: array<int, string>}|null
     */
    public function recommendedPackage(string $composerName): ?array
    {
        return match ($composerName) {
            'laravel/horizon' => [
                'name' => 'Laravel Horizon',
                'description' => 'Official dashboard and monitoring for Redis queues.',
                'tags' => ['official', 'queues', 'redis'],
                'required_services' => ['redis'],
                'post_install_commands' => ['php artisan horizon:install'],
            ],
            'laravel/scout' => [
                'name' => 'Laravel Scout',
                'description' => 'Official driver-based full-text search integration for Eloquent.',
                'tags' => ['official', 'search', 'meilisearch'],
                'required_services' => ['meilisearch'],
                'publish_commands' => ['php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"'],
            ],
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public function warnings(Package $package, Loadout $loadout): array
    {
        $enabledServices = $this->enabledServices($loadout);
        $requiredServices = $package->required_services ?? [];
        $warnings = [];

        foreach ($requiredServices as $service) {
            if (! in_array($service, $enabledServices, true)) {
                $warnings[] = "Requires {$service}; enable it in the runtime step or add it manually.";
            }
        }

        return $warnings;
    }

    /**
     * @return array<int, string>
     */
    private function enabledServices(Loadout $loadout): array
    {
        $services = [];

        if ($this->builderValue($loadout, 'runtime.cache', 'database') === 'redis') {
            $services[] = 'redis';
        }

        if ($this->builderValue($loadout, 'runtime.queue', 'database') === 'redis') {
            $services[] = 'redis';
        }

        if ($this->builderValue($loadout, 'runtime.search', 'none') === 'meilisearch') {
            $services[] = 'meilisearch';
        }

        if ($this->builderValue($loadout, 'runtime.mail', 'mailpit') === 'mailpit') {
            $services[] = 'mailpit';
        }

        return array_values(array_unique($services));
    }

    private function builderValue(Loadout $loadout, string $key, string $default): string
    {
        $value = data_get($loadout->builder_state, $key);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
