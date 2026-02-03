<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionPst\Tables;

use App\Enums\ActionScopeEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\ActionTypeEnum;
use App\Filament\Resources\ActionPst\ActionPstResource;
use App\Filament\Resources\ActionPst\Schemas\ActionForm;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Models\Service;
use App\Repository\ActionRepository;
use App\Repository\UserRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
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
            ->defaultSort('position')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(
                fn (Builder $query) => ActionRepository::findByDepartmentWithOosAndActions(
                    UserRepository::departmentSelected()
                )
            )
            ->persistFiltersInSession()
            ->recordUrl(fn (Action $record) => ActionPstResource::getUrl('view', [$record]))
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->filtersFormColumns(3)
            ->filtersFormWidth(Width::ThreeExtraLarge)
            ->recordActions([
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
            ->reorderable('position')
            ->recordUrl(fn (Action $record) => ActionPstResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->limit(120)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Numérotation')
                    ->sortable(),
            ])
            ->filters([

            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter une action')
                    ->icon('tabler-plus')
                    ->schema(fn (Schema $schema): Schema => ActionForm::configure($schema, $owner))
                    ->before(function (array $data) use ($owner): array {
                        // va pas
                        $department = $owner->department;
                        $data['department'] = $department;

                        return $data;
                    }),
            ]);
    }

    public static function full(Table $table): Table
    {
        $columns = self::getColumns();

        $columns[] = TextColumn::make('state_percentage')
            ->label('Pourcentage d\'avancement')
            ->numeric()
            ->suffix('%')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('roadmap')
            ->label('Feuille de route')
            ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('mandataries.last_name')
            ->label('Mandataires')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->formatStateUsing(
                fn ($state, Action $record) => $record->mandataries->map(
                    fn ($user) => $user->first_name.' '.$user->last_name
                )->join(', ')
            )
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('users.last_name')
            ->label('Agents pilotes')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->formatStateUsing(
                fn ($state, Action $record) => $record->users->map(fn ($user) => $user->first_name.' '.$user->last_name
                )->join(', ')
            )
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('partners.name')
            ->label('Partenaires externes')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('odds.name')
            ->label('ODD')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('description')
            ->label('Description')
            ->limit(50)
            ->html()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('evaluation_indicator')
            ->label('Indicateur d\'évaluation')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('work_plan')
            ->label('Plan de travail')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('budget_estimate')
            ->label('Budget estimé')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('financing_mode')
            ->label('Mode de financement')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('validated')
            ->label('Validé')
            ->formatStateUsing(fn ($state) => $state ? 'Oui' : 'Non')
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->persistFiltersInSession()
            ->recordUrl(fn (Action $record) => ActionPstResource::getUrl('view', [$record]))
            ->columns($columns)
            ->filters(self::getFilters())
            ->filtersFormColumns(3)
            ->filtersFormWidth(Width::ThreeExtraLarge);
    }

    private static function getFilters(): array
    {
        return [
            SelectFilter::make('operational_objectives')
                ->label('Objectif opérationel')
                ->relationship('operationalObjective', 'name')
                ->searchable(['name']),
            SelectFilter::make('state')
                ->label('État d\'avancement')
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
            SelectFilter::make('scope')
                ->label('Volet')
                ->options(ActionScopeEnum::class),
            SelectFilter::make('synergy')
                ->label(ActionSynergyEnum::getTitle())
                ->options(ActionSynergyEnum::class),
            SelectFilter::make('department')
                ->label('Département')
                ->options(UserRepository::listDepartmentOfCurrentUser())
                ->visible(fn (): bool => count(auth()->user()->departments ?? []) > 1)
                ->default(
                    count(
                        UserRepository::listDepartmentOfCurrentUser()
                    ) > 0 ? array_values(UserRepository::listDepartmentOfCurrentUser())[0] : null
                ),
            SelectFilter::make('users')
                ->label('Agents')
                ->relationship('users', 'last_name')
                //  ->modifyQueryUsing(fn(Builder $query) => $query->orderBy('last_name', 'asc'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name.' '.$record->last_name)
                ->searchable(['first_name', 'last_name']),
            SelectFilter::make('services')
                ->label('Services')
                ->options(fn () => Service::query()->orderBy('name')->pluck('name', 'id'))
                ->multiple()
                ->searchable()
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        $data['values'],
                        fn (Builder $query, array $services): Builder => $query->where(
                            fn (Builder $query) => $query
                                ->whereHas('leaderServices', fn (Builder $q) => $q->whereIn('services.id', $services))
                                ->orWhereHas('partnerServices', fn (Builder $q) => $q->whereIn('services.id', $services))
                        )
                    );
                }),
        ];
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('id')
                ->searchable()
                ->sortable()
                ->numeric()
                ->label('Id')
                ->toggleable(),
            TextColumn::make('position')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable()
                ->numeric()
                ->label('Numéro'),
            TextColumn::make('oo')
                ->label('Oo')
                ->state(fn () => 'Oo')
                ->tooltip(function (TextColumn $column): ?string {
                    $record = $column->getRecord();

                    return $record->operationalObjective?->name;
                })
                ->toggleable(),
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->label('Intitulé')
                ->limit(95)
                ->url(fn (Action $record) => ActionPstResource::getUrl('view', ['record' => $record->id]))
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (mb_strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    return $state;
                }),
            TextColumn::make('state')
                ->label('État d\'avancement')
                ->formatStateUsing(fn (ActionStateEnum $state) => $state->getLabel() ?? 'Unknown')
                ->toggleable(),
            TextColumn::make('isInternal')
                ->label('Interne')
                ->state(fn (Action $record) => $record->isInternal() ? 'Oui' : 'Non')
                ->toggleable(),
            TextColumn::make('type')
                ->formatStateUsing(fn (ActionTypeEnum $state) => $state->getLabel() ?? 'Unknown')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('synergy')
                ->label(ActionSynergyEnum::getTitle())
                ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '-')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('department')
                ->label('Département')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('leaderServices.name')
                ->label('Services porteurs')
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('partnerServices.name')
                ->label('Services partenaires')
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList()
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
        ];
    }
}
