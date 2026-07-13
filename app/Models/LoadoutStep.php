<?php

namespace App\Models;

use Database\Factories\LoadoutStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $loadout_id
 * @property string $step_key
 * @property int $position
 * @property array<string, mixed> $data
 * @property Carbon|null $completed_at
 */
#[Fillable(['loadout_id', 'step_key', 'position', 'data', 'completed_at'])]
class LoadoutStep extends Model
{
    /** @use HasFactory<LoadoutStepFactory> */
    use HasFactory;

    protected $attributes = [
        'position' => 1,
    ];

    /**
     * @return BelongsTo<Loadout, $this>
     */
    public function loadout(): BelongsTo
    {
        return $this->belongsTo(Loadout::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'data' => 'array',
            'completed_at' => 'datetime',
        ];
    }
}
