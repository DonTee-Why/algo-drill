<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Lang;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solution Model
 *
 * @property string $id
 * @property int $problem_id
 * @property Lang $lang
 * @property string|null $pseudocode
 * @property string|null $reference_code
 * @property string|null $explanation
 * @property array|null $complexity
 * @property array|null $alternatives
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Problem $problem
 */
class Solution extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'problem_id',
        'lang',
        'pseudocode',
        'reference_code',
        'explanation',
        'complexity',
        'alternatives',
    ];

    protected function casts(): array
    {
        return [
            'lang' => Lang::class,
            'complexity' => 'array',
            'alternatives' => 'array',
        ];
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
