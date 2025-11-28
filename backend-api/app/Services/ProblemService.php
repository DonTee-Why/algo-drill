<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Problem;
use Illuminate\Support\Facades\DB;

class ProblemService
{
    /**
     * Create a new problem with its signatures and tests.
     */
    public function createProblem(array $data): Problem
    {
        return DB::transaction(function () use ($data): Problem {
            $problem = Problem::create([
                'title' => $data['title'],
                'slug' => $data['slug'] ?? null,
                'difficulty' => $data['difficulty'],
                'tags' => $data['tags'],
                'constraints' => $data['constraints'],
                'description_md' => $data['description_md'],
                'is_premium' => $data['is_premium'] ?? false,
            ]);

            $this->syncSignatures($problem, $data['signatures'] ?? []);
            $this->syncTests($problem, $data['tests'] ?? []);

            return $problem;
        });
    }

    /**
     * Update an existing problem with its signatures and tests.
     */
    public function updateProblem(Problem $problem, array $data): Problem
    {
        return DB::transaction(function () use ($problem, $data): Problem {
            $problem->update([
                'title' => $data['title'],
                'slug' => $data['slug'] ?? $problem->slug,
                'difficulty' => $data['difficulty'],
                'tags' => $data['tags'],
                'constraints' => $data['constraints'],
                'description_md' => $data['description_md'],
                'is_premium' => $data['is_premium'] ?? false,
            ]);

            $this->syncSignatures($problem, $data['signatures'] ?? []);
            $this->syncTests($problem, $data['tests'] ?? []);

            return $problem->fresh();
        });
    }

    /**
     * Sync problem signatures (delete existing and create new ones).
     */
    private function syncSignatures(Problem $problem, array $signatures): void
    {
        $problem->signatures()->delete();

        foreach ($signatures as $signatureData) {
            $problem->signatures()->create($signatureData);
        }
    }

    /**
     * Sync problem tests (delete existing and create new ones).
     */
    private function syncTests(Problem $problem, array $tests): void
    {
        $problem->tests()->delete();

        foreach ($tests as $testData) {
            $problem->tests()->create($testData);
        }
    }
}
