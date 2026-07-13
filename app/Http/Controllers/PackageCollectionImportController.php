<?php

namespace App\Http\Controllers;

use App\Models\PackageCollection;
use App\Models\User;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PackageCollectionImportController extends Controller
{
    public function __invoke(Request $request, PackageCollection $packageCollection, LoadoutBuilder $builder): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $packageCollection->visibility === 'public'
            || ($user instanceof User && $packageCollection->user_id === $user->id),
            404,
        );

        $loadout = $builder->currentDraft($request->session(), $user);

        $packageCollection->load(['items.package']);

        foreach ($packageCollection->items as $item) {
            $builder->selectPackage($loadout, $item->package, $item->notes);
        }

        return to_route('builder.packages')->with('status', 'package-collection-imported');
    }
}
