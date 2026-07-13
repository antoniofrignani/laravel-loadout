<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageCollectionItemRequest;
use App\Models\PackageCollection;
use App\Models\PackageCollectionItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PackageCollectionItemController extends Controller
{
    public function store(StorePackageCollectionItemRequest $request, PackageCollection $packageCollection): RedirectResponse
    {
        $this->authorizeOwner($request, $packageCollection);

        $data = $request->itemData();

        PackageCollectionItem::query()->updateOrCreate(
            [
                'package_collection_id' => $packageCollection->id,
                'package_id' => $data['package_id'],
            ],
            [
                'notes' => $data['notes'],
                'position' => $packageCollection->items()->count(),
            ],
        );

        return back()->with('status', 'package-collection-item-added');
    }

    public function destroy(Request $request, PackageCollection $packageCollection, PackageCollectionItem $item): RedirectResponse
    {
        $this->authorizeOwner($request, $packageCollection);

        abort_unless($item->package_collection_id === $packageCollection->id, 404);

        $item->delete();

        return back()->with('status', 'package-collection-item-removed');
    }

    private function authorizeOwner(Request $request, PackageCollection $packageCollection): void
    {
        $user = $request->user();

        abort_unless($user instanceof User && $packageCollection->user_id === $user->id, 404);
    }
}
