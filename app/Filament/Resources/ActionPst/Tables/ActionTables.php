<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionPst\Tables;

use App\Constant\ActionStateEnum;
use App\Constant\ActionTypeEnum;
use App\Filament\Resources\ActionPst\Schemas\ActionForm;
use App\Filament\Resources\ActionPstResource;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Repository\UserRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ActionTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(
                fn (Builder $query) => $query->where('department', '=', UserRepository::departmentSelected())
            )
            ->recordUrl(fn (Action $record) => ActionPstResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->numeric()
                    ->label('Numéro'),
                TextColumn::make('oo')
                    ->label('Oo')
                    ->state(fn () => 'Oo')
                    ->tooltip(function (TextColumn $column): ?string {
                        $record = $column->getRecord();

                        return $record->operationalObjective?->name;
                    }),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Intitulé')
                    ->limit(110)
                    ->url(fn (Action $record) => ActionPstResource::getUrl('view', ['record' => $record->id]))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('state')
                    ->formatStateUsing(fn (ActionStateEnum $state) => $state->getLabel() ?? 'Unknown'),
                TextColumn::make('type')
                    ->formatStateUsing(fn (ActionTypeEnum $state) => $state->getLabel() ?? 'Unknown')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department')
                    ->label('Département')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('due_date')
                    ->label('Date échéance')
                    ->date()
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
                SelectFilter::make('operational_objectives')
                    ->label('Objectif opérationel')
                    ->relationship('operationalObjective', 'name')
                    ->searchable(['name']),
                SelectFilter::make('state')
                    ->label('Etat')
                    ->options(
                        collect(ActionStateEnum::cases())
                            ->mapWithKeys(fn (ActionStateEnum $action) => [$action->value => $action->getLabel()])
                            ->toArray()
                    ),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(
                        collect(ActionTypeEnum::cases())
                            ->mapWithKeys(fn (ActionTypeEnum $action) => [$action->value => $action->getLabel()])
                            ->toArray()
                    ),
                SelectFilter::make('users')
                    ->label('Agents')
                    ->relationship('users', 'first_name'),

            ])
            ->filtersFormColumns(3)
            ->filtersFormWidth(Width::ThreeExtraLarge)
            ->recordActions([
                EditAction::make()
                    ->icon('tabler-edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function actionsInline(Table $table, int $limit = 120): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->recordUrl(fn (Action $record) => ActionPstResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->limit($limit)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(
                        fn (Action $record): string => ActionPstResource::getUrl(
                            'view',
                            ['record' => $record]
                        )
                    ),
            ]);
    }

    public static function tableRelation(Table $table, Model|OperationalObjective $owner): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('name')
            ->recordUrl(fn (Action $record) => ActionPstResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->limit(120)
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([

            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter une action')
                    ->icon('tabler-plus')
                    ->schema(fn (Schema $schema): Schema => ActionForm::createForm($schema, $owner))
                    ->before(function (array $data) use ($owner): array {
                        // va pas
                        $department = $owner->department;
                        $data['department'] = $department;

                        return $data;
                    }),
            ]);
    }
}
