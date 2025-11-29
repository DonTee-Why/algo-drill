<?php

declare(strict_types=1);

namespace App\Enums;

enum Stage: string
{
    case Clarify = 'CLARIFY';
    case BruteForce = 'BRUTE_FORCE';
    case Pseudocode = 'PSEUDOCODE';
    case Code = 'CODE';
    case Test = 'TEST';
    case Optimize = 'OPTIMIZE';
    case Done = 'DONE';
}
