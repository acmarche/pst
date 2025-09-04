<?php

namespace App\Filament\Resources\ActionPst\RelationManagers;

use App\Filament\Resources\FollowUp\Tables\FollowUpTables;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FollowUpsRelationManager extends RelationManager
{
    protected static string $relationship = 'followups';
    protected static ?string $title = 'Suivi';
    protected static ?string $label = 'Suivi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return FollowupTables::configure($table);
    }
}
