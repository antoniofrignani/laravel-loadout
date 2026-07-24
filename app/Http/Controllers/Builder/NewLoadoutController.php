<?php

namespace App\Http\Controllers\Builder;

use App\Http\Controllers\Controller;
use App\Services\LoadoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewLoadoutController extends Controller
{
    public function __invoke(Request $request, LoadoutBuilder $builder): RedirectResponse
    {
        $builder->startNewDraft($request->session(), $request->user());

        return to_route('builder.project');
    }
}
