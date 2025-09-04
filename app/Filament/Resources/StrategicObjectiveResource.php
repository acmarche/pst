<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StrategicObjective\Pages;
use App\Filament\Resources\StrategicObjective\RelationManagers\OosRelationManager;
use App\Filament\Resources\StrategicObjective\Schemas\StrategicObjectiveForm;
use App\Filament\Resources\StrategicObjective\Tables\StrategicObjectiveTables;
use App\Models\StrategicObjective;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class StrategicObjectiveResource extends Resource
{
    protected static ?string $model = StrategicObjective::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Objectif Stratégique (OS)';
    }

    public static function form(Schema $schema): Schema
    {
        return StrategicObjectiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StrategicObjectiveTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStrategicObjectives::route('/'),
            'create' => Pages\CreateStrategicObjective::route('/create'),
            'view' => Pages\ViewStrategicObjective::route('/{record}'),
            'edit' => Pages\EditStrategicObjective::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }
}
