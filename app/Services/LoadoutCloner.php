<?php

namespace App\Services;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\LoadoutPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoadoutCloner
{
    public function clone(Loadout $source, ?User $user = null): Loadout
    {
        return DB::transaction(function () use ($source, $user): Loadout {
            $clone = Loadout::query()->create([
                'user_id' => $user?->id,
                'source_loadout_id' => $source->id,
                'title' => "Copy of {$source->title}",
                'slug' => $this->uniqueSlug(Str::slug("copy-of-{$source->title}")),
                'description' => $source->description,
                'visibility' => LoadoutVisibility::Private,
                'current_step' => 6,
                'builder_state' => $source->builder_state ?? [],
                'generation_snapshot' => $source->generation_snapshot,
            ]);

            /** @var LoadoutPackage $package */
            foreach ($source->packages()->orderBy('position')->get() as $package) {
                LoadoutPackage::query()->create([
                    'loadout_id' => $clone->id,
                    'package_id' => $package->package_id,
                    'package_suggestion_id' => $package->package_suggestion_id,
                    'display_name' => $package->display_name,
                    'composer_name' => $package->composer_name,
                    'npm_name' => $package->npm_name,
                    'source_url' => $package->source_url,
                    'is_custom' => $package->is_custom,
                    'package_snapshot' => $package->package_snapshot,
                    'install_options' => $package->install_options,
                    'notes' => $package->notes,
                    'position' => $package->position,
                ]);
            }

            $source->increment('clone_count');

            return $clone;
        });
    }

    private function uniqueSlug(string $base): string
    {
        $base = $base !== '' ? $base : 'loadout';

        do {
            $slug = $base.'-'.Str::lower(Str::random(8));
        } while (Loadout::query()->where('slug', $slug)->exists());

        return $slug;
    }
}
