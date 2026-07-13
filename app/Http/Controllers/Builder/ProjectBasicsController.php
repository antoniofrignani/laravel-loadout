<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\UpdateProjectBasicsRequest;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectBasicsController extends Controller
{
    public function show(Request $request, LoadoutBuilder $builder): Response
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        return Inertia::render('builder/project-basics', [
            'loadout' => [
                'title' => $loadout->title,
                'description' => $loadout->description,
            ],
            'values' => [
                'project_name' => data_get($loadout->builder_state, 'project.project_name', $loadout->title),
                'app_name' => data_get($loadout->builder_state, 'project.app_name', $loadout->title),
                'description' => data_get($loadout->builder_state, 'project.description', $loadout->description),
                'php_version' => data_get($loadout->builder_state, 'project.php_version', '8.4'),
                'laravel_version' => data_get($loadout->builder_state, 'project.laravel_version', '13.x'),
                'package_manager' => data_get($loadout->builder_state, 'project.package_manager', 'yarn'),
            ],
        ]);
    }

    public function update(UpdateProjectBasicsRequest $request, LoadoutBuilder $builder): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $validated = $request->validated();
        $state = is_array($loadout->builder_state) ? $loadout->builder_state : [];
        $state['project'] = $validated;

        $loadout->forceFill([
            'title' => $validated['project_name'],
            'description' => $validated['description'],
            'builder_state' => $state,
            'current_step' => max($loadout->current_step, 2),
        ])->save();

        return redirect()
            ->route('builder.starter')
            ->with('status', 'project-basics-saved');
    }
}
