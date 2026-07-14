<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PackageCatalog
{
    /**
     * @param  array{
     *     search?: string|null,
     *     category?: string|null,
     *     type?: string|null,
     *     featured?: bool|null,
     *     service?: string|null,
     *     laravel_version?: string|null,
     *     sort?: string|null,
     *     per_page?: int|null,
     *     exclude_ids?: array<int, int>,
     * }  $filters
     * @return LengthAwarePaginator<int, Package>
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        $query = Package::query()
            ->with('category')
            ->curated();

        $this->applySearch($query, Arr::get($filters, 'search'));
        $this->applyCategory($query, Arr::get($filters, 'category'));
        $this->applyType($query, Arr::get($filters, 'type'));
        $this->applyFeatured($query, Arr::get($filters, 'featured'));
        $this->applyRequiredService($query, Arr::get($filters, 'service'));
        $this->applyLaravelVersion($query, Arr::get($filters, 'laravel_version'));
        $this->applyExcludedIds($query, Arr::get($filters, 'exclude_ids'));
        $this->applySort($query, Arr::get($filters, 'sort'), Arr::get($filters, 'search'));

        return $query->paginate($this->perPage(Arr::get($filters, 'per_page')));
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applySearch(Builder $query, mixed $search): void
    {
        if (! is_string($search) || trim($search) === '') {
            return;
        }

        $term = Str::lower(trim($search));

        $query->where(function (Builder $query) use ($term): void {
            $query
                ->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(composer_name) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(npm_name) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"]);
        });
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyCategory(Builder $query, mixed $category): void
    {
        if (! is_string($category) || trim($category) === '') {
            return;
        }

        $slug = Str::slug($category);

        $query->whereHas('category', function (Builder $query) use ($slug): void {
            $query->where('slug', $slug);
        });
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyType(Builder $query, mixed $type): void
    {
        if ($type === 'official') {
            $query->where('is_official', true);

            return;
        }

        if ($type === 'community') {
            $query->where('is_official', false);
        }
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyFeatured(Builder $query, mixed $featured): void
    {
        if ($featured === null) {
            return;
        }

        $query->where('is_featured', filter_var($featured, FILTER_VALIDATE_BOOL));
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyRequiredService(Builder $query, mixed $service): void
    {
        if (! is_string($service) || trim($service) === '') {
            return;
        }

        $query->whereJsonContains('required_services', Str::slug($service));
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyLaravelVersion(Builder $query, mixed $laravelVersion): void
    {
        if (! is_string($laravelVersion) || trim($laravelVersion) === '') {
            return;
        }

        $query->whereJsonContains('laravel_version_constraints', trim($laravelVersion));
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyExcludedIds(Builder $query, mixed $excludedIds): void
    {
        if (! is_array($excludedIds) || $excludedIds === []) {
            return;
        }

        $ids = collect($excludedIds)
            ->filter(fn (mixed $id): bool => is_int($id))
            ->values()
            ->all();

        if ($ids === []) {
            return;
        }

        $query->whereNotIn('id', $ids);
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applySort(Builder $query, mixed $sort, mixed $search): void
    {
        match ($sort) {
            'stars' => $query->orderByDesc('stars')->orderBy('name'),
            'downloads', 'usage' => $query->orderByDesc('downloads')->orderBy('name'),
            'last_update' => $query->orderByDesc('last_source_update_at')->orderBy('name'),
            'name' => $query->orderBy('name'),
            'recently_added' => $query->latest()->orderBy('name'),
            default => $this->applyDefaultSort($query, $search),
        };
    }

    /**
     * @param  Builder<Package>  $query
     */
    private function applyDefaultSort(Builder $query, mixed $search): void
    {
        if (is_string($search) && trim($search) !== '') {
            $query->orderByDesc('is_featured')->orderByDesc('stars')->orderBy('name');

            return;
        }

        $query->orderByDesc('is_featured')->orderBy('name');
    }

    private function perPage(mixed $perPage): int
    {
        if (! is_int($perPage)) {
            return 24;
        }

        return max(1, min($perPage, 100));
    }
}
