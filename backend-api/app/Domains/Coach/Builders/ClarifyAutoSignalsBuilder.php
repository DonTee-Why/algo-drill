<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders;

use App\Models\ProblemSignature;

final class ClarifyAutoSignalsBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     mentioned_param_names: list<string>,
     *     missing_param_names: list<string>,
     *     example_count: int,
     *     has_marked_edge_case: bool
     * }
     */
    public function build(array $payload, ?ProblemSignature $signature): array
    {
        $paramNames = $this->paramNames($signature);
        $inputsOutputs = (string) ($payload['inputs_outputs'] ?? '');
        $examples = $payload['examples'] ?? '';

        $mentioned = [];
        $missing = [];

        foreach ($paramNames as $paramName) {
            if ($this->mentionsParam($inputsOutputs, $paramName)) {
                $mentioned[] = $paramName;
            } else {
                $missing[] = $paramName;
            }
        }

        return [
            'mentioned_param_names' => $mentioned,
            'missing_param_names' => $missing,
            'example_count' => $this->countExamples($examples),
            'has_marked_edge_case' => $this->hasMarkedEdgeCase($examples),
        ];
    }

    /**
     * @return list<string>
     */
    private function paramNames(?ProblemSignature $signature): array
    {
        if ($signature === null) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (array $param): string => (string) ($param['name'] ?? ''),
            $signature->params ?? []
        )));
    }

    private function mentionsParam(string $text, string $paramName): bool
    {
        if ($paramName === '') {
            return false;
        }

        return (bool) preg_match('/\b'.preg_quote($paramName, '/').'\b/i', $text);
    }

    private function countExamples(mixed $examples): int
    {
        if (is_array($examples)) {
            return count($examples);
        }

        $text = (string) $examples;

        if ($text === '') {
            return 0;
        }

        $patterns = [
            '/example\s+\d+/i',
            '/^\d+[\.\)]\s/m',
            '/^[-*]\s/m',
        ];

        $maxCount = 0;
        foreach ($patterns as $pattern) {
            $count = preg_match_all($pattern, $text) ?: 0;
            $maxCount = max($maxCount, $count);
        }

        if ($maxCount > 0) {
            return $maxCount;
        }

        $lines = array_filter(explode("\n", $text), static fn (string $line): bool => trim($line) !== '');

        return min(count($lines), 3);
    }

    private function hasMarkedEdgeCase(mixed $examples): bool
    {
        if (is_array($examples)) {
            foreach ($examples as $example) {
                if (! is_array($example)) {
                    continue;
                }

                if (($example['is_edge_case'] ?? false) === true) {
                    return true;
                }
            }

            return false;
        }

        return (bool) preg_match('/edge\s*case/i', (string) $examples);
    }
}
