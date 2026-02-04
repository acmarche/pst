<?php

namespace App\Filament\Resources\StrategicObjective\Schemas;

use App\Enums\ActionScopeEnum;
use App\Enums\DepartmentEnum;
use App\Repository\UserRepository;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class StrategicObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Identification')
                    ->icon('tabler-target')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->placeholder('Saisissez le nom de l\'objectif stratégique')
                                    ->prefixIcon('tabler-file-text')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('position')
                                    ->label('Position')
                                    ->required()
                                    ->placeholder('Ordre d\'affichage')
                                    ->prefixIcon('tabler-list-numbers')
                                    ->numeric(),
                            ]),
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
