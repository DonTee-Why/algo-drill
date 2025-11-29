<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Lang;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TestRun Model
 *
 * @property string $id
 * @property string $coaching_session_id
 * @property Lang $lang
 * @property string|null $source
 * @property array|null $result
 * @property int|null $cpu_ms
 * @property int|null $mem_kb
 * @property bool $stderr_truncated
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read CoachingSession $coachingSession
 */
class TestRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'coaching_session_id',
        'lang',
        'source',
        'result',
        'cpu_ms',
        'mem_kb',
        'stderr_truncated',
    ];

    protected function casts(): array
    {
        return [
            'lang' => Lang::class,
            'result' => 'array',
            'stderr_truncated' => 'boolean',
        ];
    }

    public function coachingSession(): BelongsTo
    {
        return $this->belongsTo(CoachingSession::class);
    }
}
