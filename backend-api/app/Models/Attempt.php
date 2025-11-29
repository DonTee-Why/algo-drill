<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Stage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attempt Model
 *
 * @property string $id
 * @property string $coaching_session_id
 * @property Stage $stage
 * @property array|null $payload
 * @property string|null $coach_msg
 * @property array|null $rubric_scores
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read CoachingSession $coachingSession
 */
class Attempt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'coaching_session_id',
        'stage',
        'payload',
        'coach_msg',
        'rubric_scores',
    ];

    protected function casts(): array
    {
        return [
            'stage' => Stage::class,
            'payload' => 'array',
            'rubric_scores' => 'array',
        ];
    }

    public function coachingSession(): BelongsTo
    {
        return $this->belongsTo(CoachingSession::class);
    }
}
