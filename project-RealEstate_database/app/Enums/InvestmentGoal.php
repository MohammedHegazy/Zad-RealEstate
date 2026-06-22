<?php

namespace App\Enums;

enum InvestmentGoal: string
{
    case PrimaryHome = 'primary_home';
    case RentalIncome = 'rental_income';
    case CapitalGrowth = 'capital_growth';
    case Flip = 'flip';
    case CommercialUse = 'commercial_use';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
