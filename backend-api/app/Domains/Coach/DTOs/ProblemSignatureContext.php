<?php

declare(strict_types=1);

namespace App\Domains\Coach\DTOs;

final class ProblemSignatureContext
{
    /**
     * @param  list<array<string, mixed>>|array<string, mixed>|null  $params
     * @param  array<string, mixed>|null  $returns
     */
    public function __construct(
        public string $functionName,
        public mixed $params,
        public mixed $returns,
    ) {}

    /**
     * @return array{function_name: string, params: mixed, returns: mixed}
     */
    public function toArray(): array
    {
        return [
            'function_name' => $this->functionName,
            'params' => $this->params,
            'returns' => $this->returns,
        ];
    }
}
