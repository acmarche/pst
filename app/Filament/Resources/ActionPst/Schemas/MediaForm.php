<?php

namespace App\Filament\Resources\ActionPst\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(
            [
                Hidden::make('file_mime'),
                Hidden::make('file_size'),
                TextInput::make('file_name')
                    ->label('Nom du média')
                    ->required()
                    ->maxLength(150),
                FileUpload::make('media')
                    ->label('Pièce jointe')
                    ->required()
                    ->maxFiles(1)
                    ->disk('public')
                    ->directory('uploads')
                    // ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    // ->preserveFilenames()
                    ->downloadable()
                    ->maxSize(10240)
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state instanceof TemporaryUploadedFile) {
                            $set('file_mime', $state->getMimeType());
                            $set('file_size', $state->getSize());
                        }
                    }),
            ]
        );
    }
}
