<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Problem Model
 *
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string $difficulty
 * @property array $tags
 * @property array $constraints
 * @property string $description_md
 * @property bool $is_premium
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProblemSignature> $signatures
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProblemTest> $tests
 */
class Problem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'difficulty',
        'tags',
        'constraints',
        'description_md',
        'is_premium',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'constraints' => 'array',
            'is_premium' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Problem $problem): void {
            if (empty($problem->slug)) {
                $problem->slug = Str::slug($problem->title);
            }
        });
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(ProblemSignature::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(ProblemTest::class);
    }
}
