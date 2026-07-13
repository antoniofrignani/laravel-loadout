<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ProfileSlugGenerator
{
    public function unique(string $name, ?int $ignoreUserId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'builder';
        $slug = $base;
        $suffix = 2;

        while ($this->exists($slug, $ignoreUserId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function exists(string $slug, ?int $ignoreUserId): bool
    {
        return User::query()
            ->where('profile_slug', $slug)
            ->when($ignoreUserId !== null, fn ($query) => $query->whereKeyNot($ignoreUserId))
            ->exists();
    }
}
