<?php

namespace App\Http\Controllers;

use App\Models\Loadout;
use App\Models\User;
use App\Services\LoadoutAccess;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoadoutResumeController extends Controller
{
    public function __invoke(Request $request, Loadout $loadout, LoadoutBuilder $builder, LoadoutAccess $access): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User && $access->canEdit($user, $loadout), 404);
        $builder->ensureEditable($loadout);

        $builder->useDraft($request->session(), $loadout);

        return to_route($this->resumeRoute($loadout))->with('status', 'loadout-resumed');
    }

    private function resumeRoute(Loadout $loadout): string
    {
        return match ($loadout->current_step) {
            1 => 'builder.project',
            2 => 'builder.starter',
            3 => 'builder.runtime',
            default => 'builder.packages',
        };
    }
}
