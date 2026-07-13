<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\StoreLoadoutPackageRequest;
use App\Http\Requests\Builder\UpdateLoadoutPackageRequest;
use App\Models\LoadoutPackage;
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

    public function update(UpdateLoadoutPackageRequest $request, LoadoutPackage $loadoutPackage, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        abort_unless($loadoutPackage->loadout_id === $loadout->id, 404);

        $validated = $request->validated();

        $loadoutPackage->forceFill([
            'notes' => $validated['notes'] ?? null,
            'install_options' => [
                'dev_dependency' => $request->boolean('install_as_dev'),
            ],
        ])->save();

        return back()->with('status', 'package-configured');
    }
}
