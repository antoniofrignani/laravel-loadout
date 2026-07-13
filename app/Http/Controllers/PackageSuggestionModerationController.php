<?php

namespace App\Http\Controllers;

use App\Models\PackageSuggestion;
use App\Services\PackageSuggestionModerator;
use Illuminate\Http\RedirectResponse;

class PackageSuggestionModerationController extends Controller
{
    public function approve(PackageSuggestion $packageSuggestion, PackageSuggestionModerator $moderator): RedirectResponse
    {
        $moderator->approve($packageSuggestion);

        return redirect()->route('builder.packages')->with('status', 'package-suggestion-approved');
    }

    public function ignore(PackageSuggestion $packageSuggestion, PackageSuggestionModerator $moderator): RedirectResponse
    {
        $moderator->ignore($packageSuggestion);

        return redirect()->route('builder.packages')->with('status', 'package-suggestion-ignored');
    }
}
