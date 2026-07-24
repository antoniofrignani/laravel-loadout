<?php

namespace App\Http\Controllers\Builder;

use App\Enums\LoadoutVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Builder\PublishLoadoutRequest;
use App\Services\LoadoutBuilder;
use App\Services\LoadoutPublisher;
use Illuminate\Http\RedirectResponse;

class PublishController extends Controller
{
    public function __invoke(PublishLoadoutRequest $request, LoadoutBuilder $builder, LoadoutPublisher $publisher): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());
        $builder->ensureEditable($loadout);

        $publisher->publish(
            $loadout,
            LoadoutVisibility::from($request->validated('visibility')),
        );

        return redirect()
            ->route('builder.review')
            ->with('status', 'loadout-published');
    }
}
