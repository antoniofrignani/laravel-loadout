<?php

namespace App\Http\Controllers;

use App\Models\Loadout;
use App\Models\User;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoadoutResumeController extends Controller
{
    public function __invoke(Request $request, Loadout $loadout, LoadoutBuilder $builder): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User && $loadout->user_id === $user->id, 404);

        $builder->useDraft($request->session(), $loadout);

        return to_route('builder.review')->with('status', 'loadout-resumed');
    }
}
