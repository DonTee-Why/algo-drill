<?php

declare(strict_types=1);

namespace App\Domains\Coach\DTOs;

final class ProblemContext
{
    /**
     * @param  list<string>|array<int, string>  $tags
     * @param  list<string>|array<int, string>  $problemConstraints
     */
    public function __construct(
        public string $title,
        public string $description,
        public array $tags,
        public array $problemConstraints,
        public ?ProblemSignatureContext $signature,
    ) {}

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     tags: list<string>|array<int, string>,
     *     problem_constraints: list<string>|array<int, string>,
     *     signature: array{function_name: string, params: mixed, returns: mixed}|null
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'tags' => $this->tags,
            'problem_constraints' => $this->problemConstraints,
            'signature' => $this->signature?->toArray(),
        ];
    }
}
