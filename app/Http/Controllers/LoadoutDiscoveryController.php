<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Http\Requests\LoadoutDiscoveryRequest;
use App\Models\Loadout;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class LoadoutDiscoveryController extends Controller
{
    public function __invoke(LoadoutDiscoveryRequest $request): Response
    {
        $filters = $request->filters();
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'popular';

        $query = Loadout::query()
            ->select([
                'id',
                'user_id',
                'title',
                'slug',
                'description',
                'visibility',
                'star_count',
                'clone_count',
                'published_at',
                'updated_at',
            ])
            ->with('user:id,name')
            ->withCount('packages')
            ->where('visibility', LoadoutVisibility::Public)
            ->whereNotNull('published_at');

        if (is_string($search) && $search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'stars' => $query->orderByDesc('star_count')->latest('published_at'),
            'clones' => $query->orderByDesc('clone_count')->latest('published_at'),
            'recent' => $query->latest('published_at')->latest('id'),
            default => $query->orderByDesc('star_count')->orderByDesc('clone_count')->latest('published_at'),
        };

        $loadouts = $query
            ->limit(24)
            ->get()
            ->map(fn (Loadout $loadout): array => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'slug' => $loadout->slug,
                'description' => $loadout->description,
                'author_name' => $loadout->user?->name,
                'packages_count' => (int) $loadout->getAttribute('packages_count'),
                'star_count' => $loadout->star_count,
                'clone_count' => $loadout->clone_count,
                'published_at' => $loadout->published_at?->toFormattedDateString(),
            ])
            ->values();

        return Inertia::render('discover/index', [
            'loadouts' => $loadouts,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
            ],
            'stats' => [
                'public_loadouts' => Loadout::query()
                    ->where('visibility', LoadoutVisibility::Public)
                    ->whereNotNull('published_at')
                    ->count(),
                'total_stars' => (int) Loadout::query()
                    ->where('visibility', LoadoutVisibility::Public)
                    ->whereNotNull('published_at')
                    ->sum('star_count'),
            ],
        ]);
    }
}
