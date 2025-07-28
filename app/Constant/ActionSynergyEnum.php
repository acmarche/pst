<?php

namespace App\Constant;

enum ActionSynergyEnum: string
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
