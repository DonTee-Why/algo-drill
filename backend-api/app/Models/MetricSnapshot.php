<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MetricSnapshot Model
 *
 * @property string $id
 * @property string $coaching_session_id
 * @property int|null $time_to_bf
 * @property int|null $passes_to_ac
 * @property int|null $edges_missed_pct
 * @property int|null $hint_count
 * @property array|null $relapse_flags
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read CoachingSession $coachingSession
 */
class MetricSnapshot extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'coaching_session_id',
        'time_to_bf',
        'passes_to_ac',
        'edges_missed_pct',
        'hint_count',
        'relapse_flags',
    ];

    protected function casts(): array
    {
        return [
            'relapse_flags' => 'array',
        ];
    }

    public function coachingSession(): BelongsTo
    {
        return $this->belongsTo(CoachingSession::class);
    }
}
