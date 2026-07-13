<?php

namespace App\Models;

use Database\Factories\PackageStatSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $package_id
 * @property string $source
 * @property int|null $stars
 * @property int|null $downloads
 * @property int|null $usage_count
 * @property Carbon|null $last_release_at
 * @property Carbon|null $last_source_update_at
 * @property Carbon $refreshed_at
 * @property string|null $error_message
 * @property array<string, mixed>|null $metadata
 */
#[Fillable([
    'package_id',
    'source',
    'stars',
    'downloads',
    'usage_count',
    'last_release_at',
    'last_source_update_at',
    'refreshed_at',
    'error_message',
    'metadata',
])]
class PackageStatSnapshot extends Model
{
    /** @use HasFactory<PackageStatSnapshotFactory> */
    use HasFactory;

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
            'stars' => 'integer',
            'downloads' => 'integer',
            'usage_count' => 'integer',
            'last_release_at' => 'datetime',
            'last_source_update_at' => 'datetime',
            'refreshed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
