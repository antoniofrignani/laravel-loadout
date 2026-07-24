<?php

namespace App\Http\Controllers;

use App\Models\Loadout;
use App\Models\User;
use App\Services\LoadoutAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoadoutLibraryController extends Controller
{
    public function __invoke(Request $request, LoadoutAccess $access): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $loadouts = Loadout::query()
            ->where(function ($query) use ($user): void {
                $query
                    ->whereBelongsTo($user)
                    ->orWhereHas('collaborators', fn ($query) => $query->whereBelongsTo($user));
            })
            ->with(['collaborators' => fn ($query) => $query->whereBelongsTo($user)])
            ->withCount('packages')
            ->latest('updated_at')
            ->get()
            ->map(function (Loadout $loadout) use ($access, $user): array {
                $role = $access->roleFor($user, $loadout);

                return [
                    'id' => $loadout->id,
                    'title' => $loadout->title,
                    'public_id' => $loadout->public_id,
                    'slug' => $loadout->slug,
                    'description' => $loadout->description,
                    'visibility' => $loadout->visibility->value,
                    'packages_count' => (int) $loadout->getAttribute('packages_count'),
                    'star_count' => $loadout->star_count,
                    'clone_count' => $loadout->clone_count,
                    'source_loadout_id' => $loadout->source_loadout_id,
                    'access_role' => $role,
                    'can_edit' => $access->canEdit($user, $loadout),
                    'can_manage_collaborators' => $access->canManageCollaborators($user, $loadout),
                    'updated_at' => $loadout->updated_at?->toFormattedDateString(),
                    'published_at' => $loadout->published_at?->toFormattedDateString(),
                ];
            });

        return Inertia::render('loadouts/index', [
            'loadouts' => $loadouts,
        ]);
    }
}
