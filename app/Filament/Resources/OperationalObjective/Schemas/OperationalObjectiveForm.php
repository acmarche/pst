<?php

namespace App\Filament\Resources\OperationalObjective\Schemas;

use App\Enums\ActionScopeEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\DepartmentEnum;
use App\Repository\UserRepository;
use Filament\Forms;
use Filament\Schemas\Schema;

final class OperationalObjectiveForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Intitulé')
                    ->maxLength(255),
                Forms\Components\Select::make('strategic_objective_id')
                    ->relationship('strategicObjective', 'name')
                    ->label('Objectif Opérationnel')
                    // ->default($owner?->id)
                    ->required(),
                Forms\Components\ToggleButtons::make('department')
                    ->label('Département')
                    ->required()
                    ->columns(4)
                    ->default(UserRepository::departmentSelected())
                    ->options(DepartmentEnum::class)
                    ->enum(DepartmentEnum::class),
                Forms\Components\ToggleButtons::make('scope')
                    ->label('Volet')
                    ->options(ActionScopeEnum::class)
                    ->inline(),
                Forms\Components\ToggleButtons::make('synergy')
                    ->label('Synergie CPAS / Ville')
                    ->options(ActionSynergyEnum::class)
                    ->inline(),
            ]);
    }
}
