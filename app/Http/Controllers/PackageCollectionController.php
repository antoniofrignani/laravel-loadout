<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageCollectionRequest;
use App\Models\Package;
use App\Models\PackageCollection;
use App\Models\PackageCollectionItem;
use App\Models\User;
use App\Services\PackageCollectionSlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageCollectionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $collections = PackageCollection::query()
            ->whereBelongsTo($user)
            ->withCount('items')
            ->latest()
            ->get()
            ->map(fn (PackageCollection $collection): array => [
                'id' => $collection->id,
                'title' => $collection->title,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'visibility' => $collection->visibility,
                'items_count' => (int) $collection->getAttribute('items_count'),
                'updated_at' => $collection->updated_at?->toFormattedDateString(),
            ]);

        return Inertia::render('package-collections/index', [
            'collections' => $collections,
        ]);
    }

    public function store(StorePackageCollectionRequest $request, PackageCollectionSlugGenerator $slugs): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $data = $request->collectionData();

        $collection = PackageCollection::query()->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $slugs->unique($data['title']),
            'description' => $data['description'],
            'visibility' => $data['visibility'],
        ]);

        return to_route('package-collections.show', $collection)->with('status', 'package-collection-created');
    }

    public function show(Request $request, PackageCollection $packageCollection): Response
    {
        $user = $request->user();

        abort_unless(
            $packageCollection->visibility === 'public'
            || ($user instanceof User && $packageCollection->user_id === $user->id),
            404,
        );

        $packageCollection->load([
            'user:id,name,profile_slug',
            'items' => fn ($query) => $query->orderBy('position')->with('package.category'),
        ]);

        return Inertia::render('package-collections/show', [
            'collection' => [
                'id' => $packageCollection->id,
                'title' => $packageCollection->title,
                'slug' => $packageCollection->slug,
                'description' => $packageCollection->description,
                'visibility' => $packageCollection->visibility,
                'can_manage' => $user instanceof User && $packageCollection->user_id === $user->id,
                'curator' => [
                    'name' => $packageCollection->user?->name,
                    'profile_slug' => $packageCollection->user?->profile_slug,
                ],
            ],
            'items' => $packageCollection->items->map(fn (PackageCollectionItem $item): array => [
                'id' => $item->id,
                'notes' => $item->notes,
                'package' => [
                    'id' => $item->package->id,
                    'name' => $item->package->name,
                    'composer_name' => $item->package->composer_name,
                    'description' => $item->package->description,
                    'stars' => $item->package->stars,
                    'downloads' => $item->package->downloads,
                    'category' => $item->package->category?->name,
                ],
            ])->values(),
            'available_packages' => $user instanceof User && $packageCollection->user_id === $user->id
                ? Package::query()
                    ->curated()
                    ->orderBy('name')
                    ->get(['id', 'name', 'composer_name'])
                : [],
        ]);
    }
}
