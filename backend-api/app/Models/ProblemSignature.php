<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Lang;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProblemSignature Model
 *
 * @property string $id
 * @property string $problem_id
 * @property Lang $lang
 * @property string $function_name
 * @property array $params
 * @property array $returns
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Problem $problem
 */
class ProblemSignature extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'problem_id',
        'lang',
        'function_name',
        'params',
        'returns',
    ];

    protected function casts(): array
    {
        return [
            'lang' => Lang::class,
            'params' => 'array',
            'returns' => 'array',
        ];
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
