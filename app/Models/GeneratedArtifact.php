<?php

namespace App\Models;

use Database\Factories\GeneratedArtifactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'loadout_id',
    'filename',
    'mime_type',
    'content_hash',
    'content',
    'metadata',
])]
class GeneratedArtifact extends Model
{
    /** @use HasFactory<GeneratedArtifactFactory> */
    use HasFactory;

    protected $attributes = [
        'mime_type' => 'text/plain',
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
            'metadata' => 'array',
        ];
    }
}
