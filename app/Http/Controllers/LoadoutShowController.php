<?php

namespace App\Http\Controllers;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\User;
use App\Services\ArtifactGenerator;
use App\Services\LoadoutAccess;
use App\Services\LoadoutOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoadoutShowController extends Controller
{
    public function __invoke(Request $request, Loadout $loadout, ArtifactGenerator $generator, LoadoutAccess $access, LoadoutOverview $overview): Response
    {
        $user = $request->user();
        $viewer = $user instanceof User ? $user : null;

        abort_unless($access->canView($viewer, $loadout), 404);

        $loadout->load(['packages' => fn ($query) => $query->orderBy('position')]);
        $isShareable = $loadout->visibility !== LoadoutVisibility::Private;

        return Inertia::render('loadouts/show', [
            'loadout' => [
                'title' => $loadout->title,
                'public_id' => $loadout->public_id,
                'slug' => $loadout->slug,
                'description' => $loadout->description,
                'visibility' => $loadout->visibility->value,
                'published_at' => $loadout->published_at?->toDateString(),
                'share_url' => $isShareable ? route('loadouts.show', $loadout) : null,
                'star_count' => $loadout->star_count,
                'viewer_has_starred' => $viewer instanceof User && $isShareable
                    ? $loadout->stars()->whereBelongsTo($viewer)->exists()
                    : false,
                'viewer_authenticated' => $viewer instanceof User,
                'viewer_is_owner' => $viewer instanceof User && $loadout->user_id === $viewer->id,
                'can_star' => $viewer instanceof User && $isShareable && $loadout->user_id !== $viewer->id,
                'can_clone' => $isShareable,
                'can_download' => $isShareable,
            ],
            'packages' => $loadout->packages->map(fn ($package): array => [
                'id' => $package->id,
                'display_name' => $package->display_name,
                'composer_name' => $package->composer_name,
                'source_url' => $package->source_url,
                'is_custom' => $package->is_custom,
            ])->values(),
            'overview' => $overview->for($loadout),
            'artifacts' => $generator->preview($loadout),
        ]);
    }
}
