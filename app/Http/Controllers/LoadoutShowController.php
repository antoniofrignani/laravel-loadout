<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Services\ArtifactGenerator;
use Inertia\Inertia;
use Inertia\Response;

class LoadoutShowController extends Controller
{
    public function __invoke(Loadout $loadout, ArtifactGenerator $generator): Response
    {
        abort_if($loadout->visibility === LoadoutVisibility::Private, 404);

        $loadout->load(['packages' => fn ($query) => $query->orderBy('position')]);

        return Inertia::render('loadouts/show', [
            'loadout' => [
                'title' => $loadout->title,
                'slug' => $loadout->slug,
                'description' => $loadout->description,
                'visibility' => $loadout->visibility->value,
                'published_at' => $loadout->published_at?->toDateString(),
                'share_url' => route('loadouts.show', $loadout),
            ],
            'packages' => $loadout->packages->map(fn ($package): array => [
                'id' => $package->id,
                'display_name' => $package->display_name,
                'composer_name' => $package->composer_name,
                'is_custom' => $package->is_custom,
            ])->values(),
            'artifacts' => $generator->preview($loadout),
        ]);
    }
}
