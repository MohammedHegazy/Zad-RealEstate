<?php

namespace App\Enums;

enum InteractionType: string
{
    case View = 'view'; 
    case Favorite = 'favorite';
    case Share = 'share';
    case ContactAgent = 'contact_agent';

    public function defaultScore(): int
    {
        return (int) config(
            "realestate.interaction_scores.{$this->value}",
            match ($this) {
                self::View => 1,
                self::Favorite => 5,
                self::Share => 3,
                self::ContactAgent => 10,
            }
        );
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
