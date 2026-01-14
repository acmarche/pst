<?php

namespace App\Filament\Resources\OperationalObjective\Schemas;

use App\Models\OperationalObjective;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

final class OperationalObjectiveForm
{
    public static function formRelation(Schema $form, Model|OperationalObjective|null $owner = null): Schema
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
                    ->default($owner?->id)
                    ->required(),
                Forms\Components\Hidden::make('department')
                    ->default($owner->department),
            ]);
    }
}
