<?php

namespace App\Services;

use App\Models\Loadout;

class LoadoutOverview
{
    /**
     * @return array<int, array{title: string, items: array<int, array{label: string, value: string}>}>
     */
    public function for(Loadout $loadout): array
    {
        return [
            [
                'title' => 'Core',
                'items' => [
                    $this->item('Laravel', $this->value($loadout, 'project.laravel_version', '13.x')),
                    $this->item('PHP', $this->value($loadout, 'project.php_version', '8.4')),
                    $this->item('Package manager', $this->optionLabel($this->value($loadout, 'project.package_manager', 'yarn'))),
                ],
            ],
            [
                'title' => 'Scaffolding',
                'items' => [
                    $this->item('Frontend', $this->optionLabel($this->value($loadout, 'starter.frontend_stack', 'react-inertia'))),
                    $this->item('Authentication', $this->optionLabel($this->value($loadout, 'starter.authentication', 'laravel-auth'))),
                    $this->item('Teams', $this->booleanValue($loadout, 'starter.teams') ? 'Included' : 'Not included'),
                    $this->item('Dark mode', $this->optionLabel($this->value($loadout, 'starter.dark_mode', 'system'))),
                ],
            ],
            [
                'title' => 'Runtime',
                'items' => [
                    $this->item('Local environment', $this->optionLabel($this->value($loadout, 'runtime.local_runtime', 'sail'))),
                    $this->item('Database', $this->optionLabel($this->value($loadout, 'runtime.database', 'mysql'))),
                    $this->item('Cache', $this->optionLabel($this->value($loadout, 'runtime.cache', 'database'))),
                    $this->item('Queue', $this->optionLabel($this->value($loadout, 'runtime.queue', 'database'))),
                    $this->item('Mail', $this->optionLabel($this->value($loadout, 'runtime.mail', 'mailpit'))),
                    $this->item('Search driver', $this->optionLabel($this->value($loadout, 'runtime.search', 'none'))),
                ],
            ],
            [
                'title' => 'Laravel foundation',
                'items' => $this->foundationItems($loadout),
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function foundationItems(Loadout $loadout): array
    {
        $items = array_values(array_filter([
            $this->value($loadout, 'starter.api_auth', 'none') !== 'none'
                ? $this->item('API authentication', $this->optionLabel($this->value($loadout, 'starter.api_auth', 'none')))
                : null,
            $this->value($loadout, 'starter.broadcasting', 'none') !== 'none'
                ? $this->item('Broadcasting', $this->optionLabel($this->value($loadout, 'starter.broadcasting', 'none')))
                : null,
            $this->value($loadout, 'starter.search', 'none') !== 'none'
                ? $this->item('Search', $this->optionLabel($this->value($loadout, 'starter.search', 'none')))
                : null,
            $this->value($loadout, 'starter.queue_monitoring', 'none') !== 'none'
                ? $this->item('Queue monitoring', $this->optionLabel($this->value($loadout, 'starter.queue_monitoring', 'none')))
                : null,
            $this->value($loadout, 'starter.observability', 'none') !== 'none'
                ? $this->item('Observability', $this->optionLabel($this->value($loadout, 'starter.observability', 'none')))
                : null,
        ]));

        return $items !== [] ? $items : [$this->item('Laravel packages', 'None selected')];
    }

    /**
     * @return array{label: string, value: string}
     */
    private function item(string $label, string $value): array
    {
        return compact('label', 'value');
    }

    private function value(Loadout $loadout, string $key, string $default): string
    {
        $value = data_get($loadout->builder_state, $key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function booleanValue(Loadout $loadout, string $key): bool
    {
        return data_get($loadout->builder_state, $key, false) === true;
    }

    private function optionLabel(string $value): string
    {
        return match ($value) {
            'react-inertia' => 'React + Inertia',
            'vue-inertia' => 'Vue + Inertia',
            'laravel-auth' => 'Laravel authentication',
            'sail' => 'Laravel Sail',
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
            'mailpit' => 'Mailpit',
            'meilisearch' => 'Meilisearch',
            'sanctum' => 'Laravel Sanctum',
            'reverb' => 'Laravel Reverb',
            'scout' => 'Laravel Scout',
            'horizon' => 'Laravel Horizon',
            'telescope' => 'Laravel Telescope',
            'pulse' => 'Laravel Pulse',
            'system' => 'Follow system',
            'enabled' => 'Enabled',
            'disabled' => 'Disabled',
            'none' => 'None',
            default => str($value)->replace('-', ' ')->headline()->toString(),
        };
    }
}
