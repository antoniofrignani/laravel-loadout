<?php

namespace App\Http\Controllers\Builder;

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
                'slug' => $loadout->slug,
            ],
            'artifacts' => $generator->preview($loadout),
        ]);
    }
}
