<?php

namespace App\Constant;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case ADMIN = 'ROLE_ADMIN';
    case RESPONSIBLE = 'ROLE_RESPONSIBLE';
    case MANDATAIRE = 'ROLE_MANDATAIRE';

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
            self::RESPONSIBLE => 'Responsable',
            self::MANDATAIRE => 'Mandataire',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => 'success',
            self::RESPONSIBLE => 'secondary',
            self::MANDATAIRE => 'primary',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ADMIN => 'Gestion des utilisateurs, des OS,OO,ODD et services',
            self::RESPONSIBLE => 'Ajout d\' actions, joindre des agents et services à celle-ci ',
            self::MANDATAIRE => 'Accès en lecture seul',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ADMIN => 'tabler-user-bolt',
            self::MANDATAIRE => 'tabler-user-circle',
            self::RESPONSIBLE => 'tabler-user-code',
        };
    }
}
