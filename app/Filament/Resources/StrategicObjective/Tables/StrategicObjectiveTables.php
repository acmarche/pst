<?php

declare(strict_types=1);

namespace App\Filament\Resources\StrategicObjective\Tables;

use App\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use App\Models\StrategicObjective;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class StrategicObjectiveTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->forSelectedDepartment()
                    ->with([
                        'oos',
                        'oos.actions',
                        'oos.actions.leaderServices',
                        'oos.actions.partnerServices',
                        'oos.actions.mandataries',
                        'oos.actions.users',
                        'oos.actions.partners',
                        'oos.actions.odds',
                    ])
            )
            ->recordTitleAttribute('name')
            ->recordUrl(fn (StrategicObjective $record) => StrategicObjectiveResource::getUrl('view', [$record]))
            ->defaultSort('position')
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

    private function saveColumns(Table $table): void
    {
        $table->columns([
            TextColumn::make('position')
                ->label('Numéro')
                ->sortable()
                ->toggleable(),
            TextColumn::make('name')
                ->label('Intitulé')
                ->limit(90)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (mb_strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    // Only render the tooltip if the column content exceeds the length limit.
                    return $state;
                })
                ->sortable()
                ->searchable(),
            TextColumn::make('oos_count')
                ->label('Objectifs Opérationnels (OO)')
                ->tooltip('Objectif Opérationnel')
                ->counts('oos')->toggleable(),
            TextColumn::make('isInternal')
                ->label('Interne')
                ->state(fn (StrategicObjective $record) => $record->isInternal() ? 'Oui' : 'Non')
                ->toggleable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);
    }
}
