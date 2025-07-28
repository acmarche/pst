<?php

namespace App\Filament\Resources\Partner\Schemas;

use App\Models\Partner;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class PartnerForm
{
    public static function configure(Schema $schema, Model|Partner|null $record = null): Schema
    {
        return $schema
            //  ->fill($record->attributesToArray())
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('initials')
                    ->default(null)
                    ->maxLength(30),
                Forms\Components\TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
