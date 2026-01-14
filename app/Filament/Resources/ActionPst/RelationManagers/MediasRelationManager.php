<?php

namespace App\Filament\Resources\ActionPst\RelationManagers;

use App\Filament\Resources\ActionPst\Schemas\MediaForm;
use App\Filament\Resources\ActionPst\Tables\MediaTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class MediasRelationManager extends RelationManager
{
    protected static string $relationship = 'medias';

    public function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return MediaTables::configure($table);
    }
}
