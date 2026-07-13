<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\User;
use App\Services\LoadoutBuilder;
use App\Services\LoadoutCloner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoadoutCloneController extends Controller
{
    public function __invoke(Request $request, Loadout $loadout, LoadoutCloner $cloner, LoadoutBuilder $builder): RedirectResponse
    {
        abort_if($loadout->visibility === LoadoutVisibility::Private, 404);

        $user = $request->user();
        $clone = $cloner->clone($loadout, $user instanceof User ? $user : null);

        $builder->useDraft($request->session(), $clone);

        return to_route('builder.review')->with('status', 'loadout-cloned');
    }
}
