<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Str;

class PackageInstallClassifier
{
    /**
     * @return array{dev_dependency: bool}
     */
    public function optionsFor(Package $package): array
    {
        return [
            'dev_dependency' => $this->isDevDependency($package),
        ];
    }

    /**
     * @param  array{composer_name: string, display_name: string}  $package
     * @return array{dev_dependency: bool}
     */
    public function optionsForCustomPackage(array $package): array
    {
        $text = Str::lower($package['composer_name'].' '.$package['display_name']);

        return [
            'dev_dependency' => $this->matchesAny($text, $this->devPackageSignals()),
        ];
    }

    private function isDevDependency(Package $package): bool
    {
        $composerName = Str::lower((string) $package->composer_name);

        if ($this->matchesAny($composerName, $this->productionPackageOverrides())) {
            return false;
        }

        if ($this->matchesAny($composerName, $this->devPackageSignals())) {
            return true;
        }

        $installCommand = Str::lower((string) $package->install_command);

        if (Str::contains($installCommand, [' --dev', ' require-dev '])) {
            return true;
        }

        $category = Str::lower((string) $package->category?->slug);

        if (in_array($category, ['developer-experience', 'testing-and-quality'], true)) {
            return true;
        }

        $text = Str::lower(implode(' ', [
            $package->name,
            $package->description,
            implode(' ', $package->tags ?? []),
        ]));

        return $this->matchesAny($text, $this->devPackageSignals());
    }

    /**
     * @return array<int, string>
     */
    private function devPackageSignals(): array
    {
        return [
            'debug',
            'debugbar',
            'ide-helper',
            'autocomplete',
            'testing',
            'phpunit',
            'pest',
            'faker',
            'fixture',
            'factory',
            'static-analysis',
            'static analysis',
            'larastan',
            'phpstan',
            'psalm',
            'pint',
            'rector',
            'code style',
            'code-quality',
            'quality',
            'local-development',
            'local development',
            'local observability',
            'telescope',
            'clockwork',
            'ray',
            'collision',
            'mockery',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function productionPackageOverrides(): array
    {
        return [
            'laravel/horizon',
            'laravel/scout',
            'laravel/sanctum',
            'laravel/cashier',
            'laravel/socialite',
            'laravel/passport',
            'spatie/laravel-permission',
            'spatie/laravel-medialibrary',
            'filament/filament',
            'meilisearch/meilisearch-php',
        ];
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function matchesAny(string $haystack, array $needles): bool
    {
        return Str::contains($haystack, $needles);
    }
}
