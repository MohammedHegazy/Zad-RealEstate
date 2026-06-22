<?php

namespace App\Enums;

enum InvestmentType: string
{
    case Residential = 'residential';
    case Commercial = 'commercial';
    case Tourist = 'tourist';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
