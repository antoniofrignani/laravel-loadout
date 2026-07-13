<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\User;
use App\Services\LoadoutStarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoadoutStarController extends Controller
{
    public function store(Request $request, Loadout $loadout, LoadoutStarService $stars): RedirectResponse
    {
        $user = $this->authorizedUser($request, $loadout);

        $stars->star($loadout, $user);

        return back();
    }

    public function destroy(Request $request, Loadout $loadout, LoadoutStarService $stars): RedirectResponse
    {
        $user = $this->authorizedUser($request, $loadout);

        $stars->unstar($loadout, $user);

        return back();
    }

    private function authorizedUser(Request $request, Loadout $loadout): User
    {
        abort_if($loadout->visibility === LoadoutVisibility::Private, 404);

        $user = $request->user();

        abort_unless($user instanceof User, 403);
        abort_if($loadout->user_id === $user->id, 422, 'You cannot star your own loadout.');

        return $user;
    }
}
