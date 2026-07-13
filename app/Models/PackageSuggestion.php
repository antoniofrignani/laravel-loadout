<?php

namespace App\Models;

use App\Enums\PackageSuggestionStatus;
use Database\Factories\PackageSuggestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $loadout_id
 * @property int|null $approved_package_id
 * @property string $input
 * @property PackageSuggestionStatus $status
 * @property Carbon|null $moderated_at
 */
#[Fillable([
    'user_id',
    'loadout_id',
    'approved_package_id',
    'input',
    'display_name',
    'composer_name',
    'npm_name',
    'source_url',
    'packagist_url',
    'github_url',
    'status',
    'lookup_payload',
    'moderated_at',
    'moderated_by',
])]
class PackageSuggestion extends Model
{
    /** @use HasFactory<PackageSuggestionFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
    ];

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
    public function loadout(): BelongsTo
    {
        return $this->belongsTo(Loadout::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function approvedPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'approved_package_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * @return HasMany<LoadoutPackage, $this>
     */
    public function loadoutPackages(): HasMany
    {
        return $this->hasMany(LoadoutPackage::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PackageSuggestionStatus::class,
            'lookup_payload' => 'array',
            'moderated_at' => 'datetime',
        ];
    }
}
