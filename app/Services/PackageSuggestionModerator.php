<?php

namespace App\Services;

use App\Enums\PackageStatus;
use App\Enums\PackageSuggestionStatus;
use App\Mail\PackageSuggestionSubmitted;
use App\Models\Package;
use App\Models\PackageSuggestion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PackageSuggestionModerator
{
    public function notify(PackageSuggestion $suggestion): void
    {
        $adminEmail = config('loadout.admin_email');

        if (! is_string($adminEmail) || $adminEmail === '') {
            return;
        }

        Mail::to($adminEmail)->send(new PackageSuggestionSubmitted($suggestion));
    }

    public function approve(PackageSuggestion $suggestion): Package
    {
        $package = Package::query()->updateOrCreate(
            ['composer_name' => $suggestion->composer_name],
            [
                'name' => $suggestion->display_name ?: Str::headline((string) Str::after($suggestion->composer_name ?? '', '/')),
                'slug' => $this->uniquePackageSlug($suggestion),
                'description' => 'Suggested by a Laravel Loadout user.',
                'source_url' => $suggestion->source_url,
                'packagist_url' => $suggestion->packagist_url,
                'github_url' => $suggestion->github_url,
                'install_command' => $suggestion->composer_name ? "composer require {$suggestion->composer_name}" : null,
                'status' => PackageStatus::Curated,
                'tags' => [],
                'required_services' => [],
            ],
        );

        $suggestion->forceFill([
            'approved_package_id' => $package->id,
            'status' => PackageSuggestionStatus::Approved,
            'moderated_at' => now(),
        ])->save();

        $suggestion->loadoutPackages()->update([
            'package_id' => $package->id,
            'package_snapshot' => [
                'id' => $package->id,
                'name' => $package->name,
                'composer_name' => $package->composer_name,
                'source_url' => $package->source_url,
                'packagist_url' => $package->packagist_url,
                'github_url' => $package->github_url,
                'install_command' => $package->install_command,
            ],
        ]);

        return $package;
    }

    public function ignore(PackageSuggestion $suggestion): void
    {
        $suggestion->forceFill([
            'status' => PackageSuggestionStatus::Ignored,
            'moderated_at' => now(),
        ])->save();
    }

    private function uniquePackageSlug(PackageSuggestion $suggestion): string
    {
        $base = Str::slug($suggestion->composer_name ?? $suggestion->display_name ?? 'package');
        $slug = $base;
        $suffix = 2;

        while (
            Package::query()
                ->where('slug', $slug)
                ->when($suggestion->composer_name, fn ($query) => $query->where('composer_name', '!=', $suggestion->composer_name))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
