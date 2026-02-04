<?php

namespace App\Filament\Resources\OperationalObjective\Schemas;

use App\Enums\ActionScopeEnum;
use App\Enums\DepartmentEnum;
use App\Repository\UserRepository;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OperationalObjectiveForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->columns(1)
            ->schema([
                Section::make('Identification')
                    ->icon('tabler-target')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Intitulé')
                            ->placeholder('Saisissez l\'intitulé de l\'objectif opérationnel')
                            ->prefixIcon('tabler-file-text')
                            ->maxLength(255),
                        Forms\Components\Select::make('strategic_objective_id')
                            ->relationship('strategicObjective', 'name')
                            ->label('Objectif Stratégique')
                            ->prefixIcon('tabler-hierarchy')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('Configuration')
                    ->icon('tabler-settings')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\ToggleButtons::make('department')
                                    ->label('Département')
                                    ->required()
                                    ->default(UserRepository::departmentSelected())
                                    ->options(DepartmentEnum::class)
                                    ->enum(DepartmentEnum::class)
                                    ->grouped(),
                                Forms\Components\ToggleButtons::make('scope')
                                    ->label('Volet')
                                    ->required()
                                    ->options(ActionScopeEnum::class)
                                    ->grouped(),
                            ]),
                    ]),
            ]);
    }
}
