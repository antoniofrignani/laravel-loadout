<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Services\LoadoutBuilder;
use App\Services\LoadoutPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function __invoke(Request $request, LoadoutBuilder $builder, LoadoutPublisher $publisher): RedirectResponse
    {
        $loadout = $builder->currentDraft($request->session(), $request->user());

        $publisher->publishUnlisted($loadout);

        return redirect()
            ->route('builder.review')
            ->with('status', 'loadout-published');
    }
}
