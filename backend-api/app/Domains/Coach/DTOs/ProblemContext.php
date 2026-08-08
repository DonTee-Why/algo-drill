<?php

declare(strict_types=1);

namespace App\Domains\Coach\DTOs;

final class ProblemContext
{
    /**
     * @param  list<string>|array<int, string>  $tags
     * @param  list<string>|array<int, string>  $constraints
     */
    public function __construct(
        public string $title,
        public string $description,
        public array $tags,
        public array $constraints,
        public string $difficulty,
        public ?ProblemSignatureContext $signature,
    ) {}

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     tags: list<string>|array<int, string>,
     *     constraints: list<string>|array<int, string>,
     *     difficulty: string,
     *     signature: array{function_name: string, params: mixed, returns: mixed}|null
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'tags' => $this->tags,
            'constraints' => $this->constraints,
            'difficulty' => $this->difficulty,
            'signature' => $this->signature?->toArray(),
        ];
    }
}
