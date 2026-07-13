<?php

namespace App\Services;

use App\Models\PackageCollection;
use Illuminate\Support\Str;

class PackageCollectionSlugGenerator
{
    public function unique(string $title, ?int $ignoreCollectionId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'collection';
        $slug = $base;
        $suffix = 2;

        while ($this->exists($slug, $ignoreCollectionId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function exists(string $slug, ?int $ignoreCollectionId): bool
    {
        return PackageCollection::query()
            ->where('slug', $slug)
            ->when($ignoreCollectionId !== null, fn ($query) => $query->whereKeyNot($ignoreCollectionId))
            ->exists();
    }
}
