<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ActionScopeEnum: string implements HasColor, HasLabel
{
    case INTERNAL = 'INTERNAL';
    case EXTERNAL = 'EXTERNAL';

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
            self::INTERNAL => 'Interne',
            self::EXTERNAL => 'Externe',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INTERNAL => 'secondary',
            self::EXTERNAL => 'primary',
        };
    }
}
