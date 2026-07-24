<?php

namespace App\Services;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;

class LoadoutPublisher
{
    public function publish(Loadout $loadout, LoadoutVisibility $visibility): Loadout
    {
        if (! $loadout->published_at) {
            $loadout->published_at = now();
        }

        $loadout->visibility = $visibility;
        $loadout->last_generated_at = now();
        $loadout->save();

        return $loadout;
    }
}
