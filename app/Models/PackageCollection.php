<?php

namespace App\Models;

use Database\Factories\PackageCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string $visibility
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'title',
    'slug',
    'description',
    'visibility',
])]
class PackageCollection extends Model
{
    /** @use HasFactory<PackageCollectionFactory> */
    use HasFactory;

    protected $attributes = [
        'visibility' => 'private',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<PackageCollectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PackageCollectionItem::class);
    }
}
