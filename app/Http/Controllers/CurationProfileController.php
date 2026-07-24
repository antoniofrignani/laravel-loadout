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
        $publicLoadouts = Loadout::query()
            ->whereBelongsTo($user)
            ->where('visibility', LoadoutVisibility::Public)
            ->whereNotNull('published_at')
            ->withCount('packages')
            ->orderByDesc('star_count')
            ->orderByDesc('clone_count')
            ->latest('published_at')
            ->get();

        $loadouts = $publicLoadouts
            ->map(fn (Loadout $loadout): array => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'public_id' => $loadout->public_id,
                'slug' => $loadout->slug,
                'description' => $loadout->description,
                'packages_count' => (int) $loadout->getAttribute('packages_count'),
                'star_count' => $loadout->star_count,
                'clone_count' => $loadout->clone_count,
                'published_at' => $loadout->published_at?->toFormattedDateString(),
            ]);

        $publicCollections = PackageCollection::query()
            ->whereBelongsTo($user)
            ->where('visibility', 'public')
            ->withCount('items')
            ->latest()
            ->get();

        $collections = $publicCollections
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
            'activity' => $this->activity($publicLoadouts->all(), $publicCollections->all()),
            'stats' => [
                'public_loadouts' => $loadouts->count(),
                'public_collections' => $collections->count(),
                'total_stars' => $loadouts->sum('star_count'),
                'total_clones' => $loadouts->sum('clone_count'),
            ],
        ]);
    }

    /**
     * @param  array<int, Loadout>  $loadouts
     * @param  array<int, PackageCollection>  $collections
     * @return array<int, array{type: string, title: string, slug: string, public_id?: string|null, description: string|null, occurred_at: string|null, metric_label: string, metric_value: int}>
     */
    private function activity(array $loadouts, array $collections): array
    {
        $activity = [];

        foreach ($loadouts as $loadout) {
            $activity[] = [
                'type' => 'loadout',
                'title' => $loadout->title,
                'slug' => $loadout->slug,
                'public_id' => $loadout->public_id,
                'description' => $loadout->description,
                'occurred_at' => $loadout->published_at?->toFormattedDateString(),
                'occurred_at_sort' => $loadout->published_at?->getTimestamp() ?? 0,
                'metric_label' => 'stars',
                'metric_value' => $loadout->star_count,
            ];
        }

        foreach ($collections as $collection) {
            $activity[] = [
                'type' => 'collection',
                'title' => $collection->title,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'occurred_at' => $collection->updated_at?->toFormattedDateString(),
                'occurred_at_sort' => $collection->updated_at?->getTimestamp() ?? 0,
                'metric_label' => 'packages',
                'metric_value' => (int) $collection->getAttribute('items_count'),
            ];
        }

        usort($activity, fn (array $a, array $b): int => $b['occurred_at_sort'] <=> $a['occurred_at_sort']);

        return array_map(function (array $item): array {
            unset($item['occurred_at_sort']);

            return $item;
        }, array_slice($activity, 0, 8));
    }
}
