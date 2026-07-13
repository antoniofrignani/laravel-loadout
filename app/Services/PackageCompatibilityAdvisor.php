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
