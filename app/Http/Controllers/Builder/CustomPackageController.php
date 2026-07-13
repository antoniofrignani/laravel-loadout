<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\StoreCustomPackageRequest;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CustomPackageController extends Controller
{
    public function store(StoreCustomPackageRequest $request, LoadoutBuilder $builder): RedirectResponse
    {
        $package = $builder->parsePackageInput($request->string('package_url')->toString());

        if (! $package) {
            throw ValidationException::withMessages([
                'package_url' => 'Enter a Packagist URL, GitHub repository URL, or vendor/package name.',
            ]);
        }

        $loadout = $builder->currentDraft($request->session(), $request->user());

        $builder->addCustomPackage($loadout, $package, $request->user());

        return back()->with('status', 'custom-package-added');
    }
}
