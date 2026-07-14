<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $package_category_id
 * @property string $name
 * @property string $slug
 * @property string|null $composer_name
 * @property string|null $npm_name
 * @property PackageStatus $status
 * @property bool $is_official
 * @property bool $is_featured
 * @property int $stars
 * @property int $downloads
 * @property int $usage_count
 * @property array<int, string>|null $required_services
 * @property array<int, string>|null $tags
 * @property Carbon|null $last_release_at
 * @property Carbon|null $last_source_update_at
 * @property Carbon|null $stats_refreshed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property PackageCategory|null $category
 */
#[Fillable([
    'package_category_id',
    'name',
    'slug',
    'composer_name',
    'npm_name',
    'description',
    'notes',
    'source_url',
    'packagist_url',
    'github_url',
    'docs_url',
    'install_command',
    'post_install_commands',
    'publish_commands',
    'migration_commands',
    'required_services',
    'laravel_version_constraints',
    'php_version_constraints',
    'tags',
    'maintainer_name',
    'status',
    'is_official',
    'is_featured',
    'stars',
    'downloads',
    'usage_count',
    'last_release_at',
    'last_source_update_at',
    'stats_refreshed_at',
])]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'curated',
        'is_official' => false,
        'is_featured' => false,
        'stars' => 0,
        'downloads' => 0,
        'usage_count' => 0,
    ];

    /**
     * @param  Builder<Package>  $query
     * @return Builder<Package>
     */
    public function scopeCurated(Builder $query): Builder
    {
        return $query->where('status', PackageStatus::Curated);
    }

    /**
     * @return BelongsTo<PackageCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'package_category_id');
    }

    /**
     * @return HasMany<PackageStatSnapshot, $this>
     */
    public function statSnapshots(): HasMany
    {
        return $this->hasMany(PackageStatSnapshot::class);
    }

    /**
     * @return HasMany<LoadoutPackage, $this>
     */
    public function loadoutPackages(): HasMany
    {
        return $this->hasMany(LoadoutPackage::class);
    }

    /**
     * @return HasMany<PackageCollectionItem, $this>
     */
    public function collectionItems(): HasMany
    {
        return $this->hasMany(PackageCollectionItem::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_install_commands' => 'array',
            'publish_commands' => 'array',
            'migration_commands' => 'array',
            'required_services' => 'array',
            'laravel_version_constraints' => 'array',
            'php_version_constraints' => 'array',
            'tags' => 'array',
            'status' => PackageStatus::class,
            'is_official' => 'boolean',
            'is_featured' => 'boolean',
            'stars' => 'integer',
            'downloads' => 'integer',
            'usage_count' => 'integer',
            'last_release_at' => 'datetime',
            'last_source_update_at' => 'datetime',
            'stats_refreshed_at' => 'datetime',
        ];
    }
}
