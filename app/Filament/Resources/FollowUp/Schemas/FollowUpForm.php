<?php

namespace App\Filament\Resources\FollowUp\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class FollowUpForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('icon')
                    ->label('Icone'),
            ]);
    }
}
