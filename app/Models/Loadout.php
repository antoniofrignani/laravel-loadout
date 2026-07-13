<?php

namespace App\Models;

use App\Enums\LoadoutVisibility;
use Carbon\CarbonInterface;
use Database\Factories\LoadoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $source_loadout_id
 * @property string $title
 * @property string $slug
 * @property LoadoutVisibility $visibility
 * @property int $current_step
 * @property string|null $description
 * @property array<string, mixed>|null $builder_state
 * @property array<string, mixed>|null $generation_snapshot
 * @property int $star_count
 * @property int $clone_count
 * @property CarbonInterface|null $published_at
 * @property CarbonInterface|null $last_generated_at
 */
#[Fillable([
    'user_id',
    'source_loadout_id',
    'title',
    'slug',
    'description',
    'visibility',
    'current_step',
    'builder_state',
    'generation_snapshot',
    'star_count',
    'clone_count',
    'published_at',
    'last_generated_at',
])]
class Loadout extends Model
{
    /** @use HasFactory<LoadoutFactory> */
    use HasFactory;

    protected $attributes = [
        'visibility' => 'private',
        'current_step' => 1,
        'star_count' => 0,
        'clone_count' => 0,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Loadout>  $query
     * @return Builder<Loadout>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', LoadoutVisibility::Public);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Loadout, $this>
     */
    public function sourceLoadout(): BelongsTo
    {
        return $this->belongsTo(Loadout::class, 'source_loadout_id');
    }

    /**
     * @return HasMany<Loadout, $this>
     */
    public function clones(): HasMany
    {
        return $this->hasMany(Loadout::class, 'source_loadout_id');
    }

    /**
     * @return HasMany<LoadoutStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(LoadoutStep::class);
    }

    /**
     * @return HasMany<LoadoutPackage, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(LoadoutPackage::class);
    }

    /**
     * @return HasMany<GeneratedArtifact, $this>
     */
    public function generatedArtifacts(): HasMany
    {
        return $this->hasMany(GeneratedArtifact::class);
    }

    /**
     * @return HasMany<PackageSuggestion, $this>
     */
    public function packageSuggestions(): HasMany
    {
        return $this->hasMany(PackageSuggestion::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => LoadoutVisibility::class,
            'current_step' => 'integer',
            'builder_state' => 'array',
            'generation_snapshot' => 'array',
            'star_count' => 'integer',
            'clone_count' => 'integer',
            'published_at' => 'datetime',
            'last_generated_at' => 'datetime',
        ];
    }
}
