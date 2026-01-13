<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('last_name')
            ->recordUrl(fn (User $record) => UserResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->icon('tabler-phone'),
                TextColumn::make('extension')
                    ->label('Extension')
                    ->icon('tabler-device-landline-phone'),
                TextColumn::make('roles.label'),
                TextColumn::make('departments')
                    ->label('Départements')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('username')
                    ->label('Nom d\'utilisateur')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'label'),
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
