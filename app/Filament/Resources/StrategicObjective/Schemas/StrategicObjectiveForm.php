<?php

namespace App\Filament\Resources\StrategicObjective\Schemas;

use App\Enums\ActionScopeEnum;
use App\Enums\DepartmentEnum;
use App\Repository\UserRepository;
use Filament\Forms;
use Filament\Schemas\Schema;

final class StrategicObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\ToggleButtons::make('department')
                    ->label('Département')
                    ->required()
                    ->columns(4)
                    ->default(UserRepository::departmentSelected())
                    ->options(DepartmentEnum::class)
                    ->enum(DepartmentEnum::class)
                    ->visible($schema->getOperation() === 'create'),
                Forms\Components\ToggleButtons::make('scope')
                    ->label('Volet')
                    ->helperText('Si interne, l\'objectif stratégique sera visible par la Ville et les CPAS.')
                    ->options(ActionScopeEnum::class)
                    ->inline(),
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->numeric(),
            ]);
    }
}
