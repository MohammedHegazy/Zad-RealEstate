<?php

namespace App\Enums;

/**
 * Intended use / function of the property the user is looking for.
 * 
 */
enum PropertyFunction: string
{
    case Buy = 'buy';
    case Rent = 'rent';
    case Invest = 'invest';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
