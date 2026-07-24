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
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $public_id
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
        return 'public_id';
    }

    public function isLocked(): bool
    {
        return $this->published_at !== null;
    }

    public function projectSlug(): string
    {
        $projectName = data_get($this->builder_state, 'project.project_name');

        return Str::slug(is_string($projectName) ? $projectName : $this->title) ?: 'laravel-starter';
    }

    public function downloadFilename(): string
    {
        return 'laravel-loadout-'.$this->projectSlug().'.zip';
    }

    protected static function booted(): void
    {
        static::saving(function (Loadout $loadout): void {
            $loadout->public_id ??= (string) Str::ulid();
        });
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
     * @return HasMany<LoadoutStar, $this>
     */
    public function stars(): HasMany
    {
        return $this->hasMany(LoadoutStar::class);
    }

    /**
     * @return HasMany<LoadoutCollaborator, $this>
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(LoadoutCollaborator::class);
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
