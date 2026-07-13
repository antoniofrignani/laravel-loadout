<?php

namespace App\Http\Controllers;

use App\Models\Loadout;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoadoutLibraryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $loadouts = Loadout::query()
            ->whereBelongsTo($user)
            ->withCount('packages')
            ->latest('updated_at')
            ->get()
            ->map(fn (Loadout $loadout): array => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'slug' => $loadout->slug,
                'description' => $loadout->description,
                'visibility' => $loadout->visibility->value,
                'packages_count' => (int) $loadout->getAttribute('packages_count'),
                'star_count' => $loadout->star_count,
                'clone_count' => $loadout->clone_count,
                'source_loadout_id' => $loadout->source_loadout_id,
                'updated_at' => $loadout->updated_at?->toFormattedDateString(),
                'published_at' => $loadout->published_at?->toFormattedDateString(),
            ]);

        return Inertia::render('loadouts/index', [
            'loadouts' => $loadouts,
        ]);
    }
}
