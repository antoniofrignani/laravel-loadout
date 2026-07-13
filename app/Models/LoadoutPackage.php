<?php

namespace App\Models;

use Database\Factories\LoadoutPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $loadout_id
 * @property int|null $package_id
 * @property int|null $package_suggestion_id
 * @property string $display_name
 * @property string|null $composer_name
 * @property string|null $npm_name
 * @property string|null $source_url
 * @property bool $is_custom
 * @property array<string, mixed>|null $package_snapshot
 * @property array<string, mixed>|null $install_options
 * @property string|null $notes
 * @property int $position
 */
#[Fillable([
    'loadout_id',
    'package_id',
    'package_suggestion_id',
    'display_name',
    'composer_name',
    'npm_name',
    'source_url',
    'is_custom',
    'package_snapshot',
    'install_options',
    'notes',
    'position',
])]
class LoadoutPackage extends Model
{
    /** @use HasFactory<LoadoutPackageFactory> */
    use HasFactory;

    protected $attributes = [
        'is_custom' => false,
        'position' => 0,
    ];

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
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return BelongsTo<PackageSuggestion, $this>
     */
    public function packageSuggestion(): BelongsTo
    {
        return $this->belongsTo(PackageSuggestion::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_custom' => 'boolean',
            'package_snapshot' => 'array',
            'install_options' => 'array',
            'position' => 'integer',
        ];
    }
}
