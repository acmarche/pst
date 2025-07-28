<?php

namespace App\Constant;

use Filament\Support\Contracts\HasLabel;

enum ActionRoadmapEnum: string implements HasLabel
{
    case YES = "YES";
    case NO = "NO";

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::YES => 'Oui',
            self::NO => 'Non',
        };
    }

}
