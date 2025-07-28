<?php

namespace App\Constant;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasColor, HasLabel, HasDescription, HasIcon
{
    case ADMIN = "ROLE_ADMIN";
    case MANAGER = "ROLE_MANAGER";
    case AGENT = "ROLE_AGENT";
    case MANDATAIRE = "ROLE_MANDATAIRE";

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
            self::ADMIN => 'Administrateur',
            self::AGENT => 'Agent',
            self::MANDATAIRE => 'Mandataire',
            self::MANAGER => 'Manageur',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => 'success',
            self::AGENT => 'warning',
            self::MANAGER => 'secondary',
            self::MANDATAIRE => 'primary',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Gestion des utilisateurs et des services',
            self::AGENT => 'Rôle standard, gestion des actions',
            self::MANAGER => 'Gestion des OS,OO,ODD et partenaires',
            self::MANDATAIRE => 'Accès en lecture seul',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ADMIN => 'tabler-user-bolt',
            self::AGENT => 'tabler-user',
            self::MANDATAIRE => 'tabler-user-circle',
            self::MANAGER => 'tabler-user-code',
        };
    }
}
