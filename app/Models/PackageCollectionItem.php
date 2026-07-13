<?php

namespace App\Models;

use Database\Factories\PackageCollectionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $package_collection_id
 * @property int $package_id
 * @property string|null $notes
 * @property int $position
 */
#[Fillable([
    'package_collection_id',
    'package_id',
    'notes',
    'position',
])]
class PackageCollectionItem extends Model
{
    /** @use HasFactory<PackageCollectionItemFactory> */
    use HasFactory;

    protected $attributes = [
        'position' => 0,
    ];

    /**
     * @return BelongsTo<PackageCollection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(PackageCollection::class, 'package_collection_id');
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
