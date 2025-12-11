<?php

declare(strict_types=1);

namespace App\Enums;

enum Stage: string
{
    case Clarify = 'CLARIFY';
    case Approach = 'APPROACH';
    case Pseudocode = 'PSEUDOCODE';
    case BruteForce = 'BRUTE_FORCE';
    case Optimize = 'OPTIMIZE';
    case Done = 'DONE';

    /**
     * Determine if this stage requires code submission
     */
    public function requiresCode(): bool
    {
        return in_array($this, [self::BruteForce, self::Optimize]);
    }

    /**
     * Determine if this stage requires text/articulation submission
     */
    public function requiresText(): bool
    {
        return in_array($this, [self::Clarify, self::Approach, self::Pseudocode, self::Optimize]);
    }

    /**
     * Determine if this stage is purely text (no code)
     */
    public function isTextOnly(): bool
    {
        return in_array($this, [self::Clarify, self::Approach, self::Pseudocode]);
    }

    /**
     * Determine if this stage is purely code (no text explanation required)
     */
    public function isCodeOnly(): bool
    {
        return $this === self::BruteForce;
    }

    /**
     * Determine if this stage requires both code AND text
     */
    public function isHybridStage(): bool
    {
        return $this === self::Optimize;
    }

    /**
     * Get the next stage in the workflow
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Clarify => self::Approach,
            self::Approach => self::Pseudocode,
            self::Pseudocode => self::BruteForce,
            self::BruteForce => self::Optimize,
            self::Optimize => self::Done,
            self::Done => null,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
