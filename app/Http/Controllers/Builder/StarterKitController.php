<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\UpdateStarterKitRequest;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StarterKitController extends Controller
{
    public function show(Request $request, LoadoutBuilder $builder): Response
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        return Inertia::render('builder/starter-kit', [
            'values' => [
                'frontend_stack' => data_get($loadout->builder_state, 'starter.frontend_stack', 'react-inertia'),
                'authentication' => data_get($loadout->builder_state, 'starter.authentication', 'laravel-auth'),
                'teams' => data_get($loadout->builder_state, 'starter.teams', false),
                'dark_mode' => data_get($loadout->builder_state, 'starter.dark_mode', 'system'),
            ],
        ]);
    }

    public function update(UpdateStarterKitRequest $request, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $validated = $request->validated();
        $validated['teams'] = $request->boolean('teams');
        $state = $loadout->builder_state ?? [];
        $state['starter'] = $validated;

        $loadout->forceFill([
            'builder_state' => $state,
            'current_step' => max($loadout->current_step, 3),
        ])->save();

        return redirect()
            ->route('builder.runtime')
            ->with('status', 'starter-kit-saved');
    }
}
