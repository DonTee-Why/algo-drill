<?php

declare(strict_types=1);

namespace App\Enums;

enum Lang: string
{
    case Javascript = 'javascript';
    case Python = 'python';
    case Php = 'php';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
