<?php


namespace App\Filament\Resources\Users\Schemas;

use App\Constant\DepartmentEnum;
use Filament\Forms\Components\CheckboxList;
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
                    ->relationship('roles', 'name'),
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
                    ->columns(2)
            ]);
    }
}
