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
            'is_locked' => $loadout->isLocked(),
            'values' => [
                'frontend_stack' => data_get($loadout->builder_state, 'starter.frontend_stack', 'react-inertia'),
                'authentication' => data_get($loadout->builder_state, 'starter.authentication', 'laravel-auth'),
                'teams' => data_get($loadout->builder_state, 'starter.teams', false),
                'dark_mode' => data_get($loadout->builder_state, 'starter.dark_mode', 'system'),
                'api_auth' => data_get($loadout->builder_state, 'starter.api_auth', 'none'),
                'broadcasting' => data_get($loadout->builder_state, 'starter.broadcasting', 'none'),
                'search' => data_get($loadout->builder_state, 'starter.search', 'none'),
                'queue_monitoring' => data_get($loadout->builder_state, 'starter.queue_monitoring', 'none'),
                'observability' => data_get($loadout->builder_state, 'starter.observability', 'none'),
            ],
        ]);
    }

    public function update(UpdateStarterKitRequest $request, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $builder->ensureEditable($loadout);
        $validated = $request->validated();
        $validated['teams'] = $request->boolean('teams');
        $validated['api_auth'] ??= 'none';
        $validated['broadcasting'] ??= 'none';
        $validated['search'] ??= 'none';
        $validated['queue_monitoring'] ??= 'none';
        $validated['observability'] ??= 'none';
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
