<?php

namespace App\Services;

use App\Enums\LoadoutVisibility;
use App\Models\Loadout;
use App\Models\User;

class LoadoutAccess
{
    public function roleFor(User $user, Loadout $loadout): ?string
    {
        if ($loadout->user_id === $user->id) {
            return 'owner';
        }

        if ($loadout->relationLoaded('collaborators')) {
            $role = $loadout->collaborators
                ->firstWhere('user_id', $user->id)
                ?->role;

            return is_string($role) ? $role : null;
        }

        $role = $loadout->collaborators()
            ->whereBelongsTo($user)
            ->value('role');

        return is_string($role) ? $role : null;
    }

    public function canEdit(User $user, Loadout $loadout): bool
    {
        return in_array($this->roleFor($user, $loadout), ['owner', 'editor'], true);
    }

    public function canView(?User $user, Loadout $loadout): bool
    {
        if ($loadout->visibility !== LoadoutVisibility::Private) {
            return true;
        }

        return $user instanceof User && $this->roleFor($user, $loadout) !== null;
    }

    public function canManageCollaborators(User $user, Loadout $loadout): bool
    {
        return $this->roleFor($user, $loadout) === 'owner';
    }
}
