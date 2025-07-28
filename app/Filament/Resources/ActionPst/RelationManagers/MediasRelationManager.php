<?php

namespace App\Filament\Resources\ActionPst\RelationManagers;

use App\Filament\Resources\ActionPst\Schemas\ActionForm;
use App\Models\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Storage;

class MediasRelationManager extends RelationManager
{
    protected static string $relationship = 'medias';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema(ActionForm::fieldsAttachment());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('download')
                    ->label('Téléchargement')
                    ->state('Télécharger')
                    ->icon('tabler-download')
                    ->action(fn(Media $media) => Storage::disk('public')->download($media->file_name)),
                Tables\Columns\TextColumn::make('size'),
                Tables\Columns\TextColumn::make('mime_type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
