<?php

namespace App\Filament\Resources\Service\Schemas;

use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ServiceForm
{
    public static function configure(Schema $schema, Model|Service|null $record = null): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('initials')
                    ->maxLength(30),
                Forms\Components\Select::make('users')
                    ->label('Agents membres')
                    ->relationship('users', 'last_name')
                    ->searchable(['first_name', 'last_name'])
                    ->getOptionLabelFromRecordUsing(fn(User $user) => $user->first_name.' '.$user->last_name)
                    ->multiple()
                    ->columnSpanFull(),
            ]);
    }
}
