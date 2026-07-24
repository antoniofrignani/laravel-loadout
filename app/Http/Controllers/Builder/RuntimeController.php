<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\UpdateRuntimeRequest;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RuntimeController extends Controller
{
    public function show(Request $request, LoadoutBuilder $builder): Response
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        return Inertia::render('builder/runtime', [
            'is_locked' => $loadout->isLocked(),
            'values' => [
                'database' => data_get($loadout->builder_state, 'runtime.database', 'mysql'),
                'cache' => data_get($loadout->builder_state, 'runtime.cache', 'database'),
                'queue' => data_get($loadout->builder_state, 'runtime.queue', 'database'),
                'mail' => data_get($loadout->builder_state, 'runtime.mail', 'mailpit'),
                'search' => data_get($loadout->builder_state, 'runtime.search', 'none'),
                'local_runtime' => data_get($loadout->builder_state, 'runtime.local_runtime', 'sail'),
            ],
        ]);
    }

    public function update(UpdateRuntimeRequest $request, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $builder->ensureEditable($loadout);
        $state = $loadout->builder_state ?? [];
        $state['runtime'] = $request->validated();

        $loadout->forceFill([
            'builder_state' => $state,
            'current_step' => max($loadout->current_step, 4),
        ])->save();

        return redirect()
            ->route('builder.packages')
            ->with('status', 'runtime-saved');
    }
}
