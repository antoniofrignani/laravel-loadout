<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\PackageCollection;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class CurationProfileController extends Controller
{
    public function __invoke(User $user): Response
    {
        $loadouts = Loadout::query()
            ->whereBelongsTo($user)
            ->where('visibility', LoadoutVisibility::Public)
            ->whereNotNull('published_at')
            ->withCount('packages')
            ->orderByDesc('star_count')
            ->orderByDesc('clone_count')
            ->latest('published_at')
            ->get()
            ->map(fn (Loadout $loadout): array => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'slug' => $loadout->slug,
                'description' => $loadout->description,
                'packages_count' => (int) $loadout->getAttribute('packages_count'),
                'star_count' => $loadout->star_count,
                'clone_count' => $loadout->clone_count,
                'published_at' => $loadout->published_at?->toFormattedDateString(),
            ]);

        $collections = PackageCollection::query()
            ->whereBelongsTo($user)
            ->where('visibility', 'public')
            ->withCount('items')
            ->latest()
            ->get()
            ->map(fn (PackageCollection $collection): array => [
                'id' => $collection->id,
                'title' => $collection->title,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'items_count' => (int) $collection->getAttribute('items_count'),
            ]);

        return Inertia::render('profiles/show', [
            'profile' => [
                'name' => $user->name,
                'profile_slug' => $user->profile_slug,
                'profile_bio' => $user->profile_bio,
                'joined_at' => $user->created_at?->toFormattedDateString(),
            ],
            'loadouts' => $loadouts,
            'collections' => $collections,
            'stats' => [
                'public_loadouts' => $loadouts->count(),
                'public_collections' => $collections->count(),
                'total_stars' => $loadouts->sum('star_count'),
                'total_clones' => $loadouts->sum('clone_count'),
            ],
        ]);
    }
}
