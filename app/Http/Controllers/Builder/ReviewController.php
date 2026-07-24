<?php

namespace App\Http\Controllers\Builder;

use App\Enums\LoadoutVisibility;
use App\Http\Controllers\Controller;
use App\Services\ArtifactGenerator;
use App\Services\LoadoutBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function __invoke(Request $request, LoadoutBuilder $builder, ArtifactGenerator $generator): Response
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        return Inertia::render('builder/review', [
            'loadout' => [
                'id' => $loadout->id,
                'title' => $loadout->title,
                'public_id' => $loadout->public_id,
                'slug' => $loadout->slug,
                'visibility' => $loadout->visibility->value,
                'is_locked' => $loadout->isLocked(),
                'is_shareable' => $loadout->visibility !== LoadoutVisibility::Private,
                'share_url' => $loadout->visibility !== LoadoutVisibility::Private
                    ? route('loadouts.show', $loadout)
                    : null,
            ],
            'artifacts' => $generator->preview($loadout),
        ]);
    }
}
