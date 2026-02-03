<?php

namespace App\Filament\Resources\Service\Tables;

use App\Filament\Resources\Service\ServiceResource;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ServiceTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('name')
            ->recordUrl(fn (Service $record) => ServiceResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('initials')
                    ->label('Initiales')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('users_count')
                    ->label('Agents')
                    ->counts('users')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('leading_actions_for_department_count')
                    ->label('Actions pilotées')
                    ->counts('leadingActionsForDepartment')
                    ->sortable(),
                TextColumn::make('partnering_actions_for_department_count')
                    ->label('Actions partenaires')
                    ->counts('partneringActionsForDepartment')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
