<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProblemTest Model
 *
 * @property string $id
 * @property string $problem_id
 * @property array $input
 * @property mixed $expected
 * @property bool $is_edge
 * @property int $weight
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Problem $problem
 */
class ProblemTest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'problem_id',
        'input',
        'expected',
        'is_edge',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'expected' => 'array',
            'is_edge' => 'boolean',
            'weight' => 'integer',
        ];
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
