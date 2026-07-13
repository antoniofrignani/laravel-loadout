<?php

namespace App\Services;

use App\Models\Loadout;
use App\Models\LoadoutStar;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoadoutStarService
{
    public function star(Loadout $loadout, User $user): void
    {
        DB::transaction(function () use ($loadout, $user): void {
            $star = LoadoutStar::query()->firstOrCreate([
                'loadout_id' => $loadout->id,
                'user_id' => $user->id,
            ]);

            if ($star->wasRecentlyCreated) {
                $loadout->increment('star_count');
            }
        });
    }

    public function unstar(Loadout $loadout, User $user): void
    {
        DB::transaction(function () use ($loadout, $user): void {
            $deleted = LoadoutStar::query()
                ->where('loadout_id', $loadout->id)
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted > 0) {
                Loadout::query()
                    ->whereKey($loadout->id)
                    ->where('star_count', '>', 0)
                    ->decrement('star_count');
            }
        });
    }
}
