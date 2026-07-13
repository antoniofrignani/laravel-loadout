<?php

namespace App\Services;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;

class LoadoutPublisher
{
    public function publishUnlisted(Loadout $loadout): Loadout
    {
        if (! $loadout->published_at) {
            $loadout->published_at = now();
        }

        $loadout->visibility = LoadoutVisibility::Unlisted;
        $loadout->last_generated_at = now();
        $loadout->save();

        return $loadout;
    }
}
