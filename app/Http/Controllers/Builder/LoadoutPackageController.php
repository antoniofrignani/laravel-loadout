<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\StoreLoadoutPackageRequest;
use App\Models\Package;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;

class LoadoutPackageController extends Controller
{
    public function store(StoreLoadoutPackageRequest $request, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $package = Package::query()->findOrFail($request->integer('package_id'));

        $builder->selectPackage($loadout, $package);

        return back()->with('status', 'package-selected');
    }
}
