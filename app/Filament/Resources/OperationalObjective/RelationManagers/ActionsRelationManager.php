<?php

namespace App\Filament\Resources\OperationalObjective\RelationManagers;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->actions()->count().' Actions';
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return ActionTables::tableRelation($table, $this->ownerRecord);
    }
}
