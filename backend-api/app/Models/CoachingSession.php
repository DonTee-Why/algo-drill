<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Stage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CoachingSession Model
 *
 * @property string $id
 * @property int $user_id
 * @property int $problem_id
 * @property Stage $state
 * @property array|null $scores
 * @property array|null $hints_used
 * @property array|null $timers
 * @property array|null $revealed_langs
 * @property \Illuminate\Support\Carbon|null $revealed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read Problem $problem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attempt> $attempts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TestRun> $testRuns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MetricSnapshot> $metricSnapshots
 */
class CoachingSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'problem_id',
        'state',
        'scores',
        'hints_used',
        'timers',
        'revealed_langs',
        'revealed_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => Stage::class,
            'scores' => 'array',
            'hints_used' => 'array',
            'timers' => 'array',
            'revealed_langs' => 'array',
            'revealed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class);
    }

    public function metricSnapshots(): HasMany
    {
        return $this->hasMany(MetricSnapshot::class);
    }
}
