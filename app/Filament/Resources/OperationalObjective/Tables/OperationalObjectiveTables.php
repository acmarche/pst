<?php

namespace App\Filament\Resources\OperationalObjective\Tables;

use App\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use App\Models\OperationalObjective;
use App\Models\StrategicObjective;
use App\Repository\OperationalObjectiveRepository;
use App\Repository\UserRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class OperationalObjectiveTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(
                fn(Builder $query) => OperationalObjectiveRepository::findByDepartmentWithOosAndActions(UserRepository::departmentSelected())
            )
            ->recordUrl(fn(OperationalObjective $record) => OperationalObjectiveResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('position')
                    ->label('Numéro')
                    ->state(
                        fn(OperationalObjective $objective
                        ): string => $objective->strategicObjective?->position . '.' . ' ' . $objective->position
                    )
                    ->sortable(),
                TextColumn::make('os')
                    ->label('Os')
                    ->state(fn() => 'Os')
                    ->tooltip(function (TextColumn $column): ?string {
                        $record = $column->getRecord();

                        return $record->strategicObjective?->name;
                    }),
                TextColumn::make('name')
                    ->searchable()
                    ->icon(fn(OperationalObjective $record) => $record->isInternal() ? Heroicon::LightBulb : false)
                    ->iconPosition(IconPosition::After)
                    ->suffix(fn(OperationalObjective $record) => $record->isInternal() ? '(Interne)' : '')
                    ->sortable()
                    ->limit(85)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('actions_count')
                    ->label('Actions')
                    ->counts('actions')
                    ->sortable(),
                TextColumn::make('department')
                    ->label('Département')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make()
                    ->icon('tabler-edit'),
                DeleteAction::make()
                    ->icon('tabler-trash'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function tableInline(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('actions_count')
                    ->label('Actions')
                    ->counts('actions'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un Oo')
                    ->icon('tabler-plus')
                    ->before(function (array $data): array {
                        // va pas
                        $strategicObjective = StrategicObjective::find($data['strategic_objective_id']);
                        $data['department'] = $strategicObjective->department;

                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(
                        fn(OperationalObjective $record): string => OperationalObjectiveResource::getUrl(
                            'view',
                            ['record' => $record]
                        )
                    ),
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
