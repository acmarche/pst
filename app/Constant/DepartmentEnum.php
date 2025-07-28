<?php

namespace App\Constant;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DepartmentEnum: string implements HasColor, HasLabel
{
    case CPAS = "CPAS";
    case VILLE = "VILLE";
    case COMMON = "COMMUN";

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
            self::CPAS => 'Cpas',
            self::VILLE => 'Ville',
            self::COMMON => 'Commun',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CPAS => 'primary',
            self::VILLE => 'success',
            self::COMMON => 'warning',
        };
    }
}
