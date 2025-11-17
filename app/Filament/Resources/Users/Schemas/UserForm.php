<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Constant\DepartmentEnum;
use App\Models\Role;
use App\Repository\UserRepository;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                CheckboxList::make('roles')
                    ->label('Rôles')
                    ->relationship('roles', 'label')
                    ,
                ToggleButtons::make('departments')
                    ->label('Département(s)')
                    ->default(DepartmentEnum::VILLE->value)
                    ->options(
                        [
                            DepartmentEnum::VILLE->value => DepartmentEnum::VILLE->getLabel(),
                            DepartmentEnum::CPAS->value => DepartmentEnum::CPAS->getLabel(),
                        ]
                    )
                    ->multiple()
                    ->required(),
                CheckboxList::make('services')
                    ->label('Services')
                    ->relationship('services', 'name')
                    ->columns(2),
            ]);
    }

    public static function add(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('username')
                    ->label('Nom')
                    ->options(UserRepository::listUsersFromLdapForSelect())
                    ->searchable(),
                ToggleButtons::make('departments')
                    ->label('Département(s)')
                    ->default(DepartmentEnum::VILLE->value)
                    ->options(
                        [
                            DepartmentEnum::VILLE->value => DepartmentEnum::VILLE->getLabel(),
                            DepartmentEnum::CPAS->value => DepartmentEnum::CPAS->getLabel(),
                        ]
                    )
                    ->multiple()
                    ->required(),
                CheckboxList::make('roles')
                    ->label('Rôles')
                    ->relationship('roles', 'label'),
            ]);
    }
}
