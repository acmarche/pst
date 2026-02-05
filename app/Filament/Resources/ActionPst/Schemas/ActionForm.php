<?php

namespace App\Filament\Resources\ActionPst\Schemas;

use App\Enums\ActionRoadmapEnum;
use App\Enums\ActionScopeEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\ActionTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\YesOrNoEnum;
use App\Models\OperationalObjective;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class ActionForm
{
    public static function configure(Schema $schema, Model|OperationalObjective|null $owner): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Forms\Components\Hidden::make('department')
                    ->default($owner?->department),
                Wizard::make([
                    Wizard\Step::make('project')
                        ->label('Projet')
                        ->schema(
                            self::fieldsProject($owner),
                        ),
                    Wizard\Step::make('team')
                        ->label('Equipes')
                        ->schema(self::fieldsTeam())
                        ->visible(
                            fn (
                                Action|Model|null $record = null,
                                ?string $operation = null
                            ) => $operation === 'create' || ($record !== null && Gate::check(
                                'teams-edit',
                                [
                                    $record,
                                    $operation,
                                ]
                            ))
                        ),
                    Wizard\Step::make('info')
                        ->label('Informations')
                        ->schema(
                            self::fieldsDescription(),
                        ),
                    Wizard\Step::make('odd')
                        ->label('Odds')
                        ->schema(
                            self::fieldsOdd(),
                        ),
                    Wizard\Step::make('financing')
                        ->label('Financement')
                        ->schema(
                            self::fieldsFinancing(),
                        ),
                ])
                    ->skippable()
                    ->nextAction(
                        fn (Action $action) => $action
                            ->label('Suivant')
                            ->color('success'),
                    )->previousAction(
                        fn (Action $action) => $action
                            ->label('Précédent')
                            ->color('secondary'),
                    )
                    ->submitAction(view('components.btn_add')),
            ]);
    }

    public static function fieldsReminder(): array
    {
        return
            [
                Forms\Components\Select::make('recipients')
                    ->label('Destinataires')
                    ->options(fn () => User::query()
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn ($user) => [$user->id => "{$user->last_name} {$user->first_name}"]))
                    ->multiple()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('subject')
                    ->label('Sujet')
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('Contenu')
                    ->required(),
            ];
    }

    public static function fieldsExportPdf(): array
    {
        return
            [

            ];
    }

    private static function fieldsProject(Model|OperationalObjective|null $owner): array
    {
        return [
            Section::make('Identification')
                ->columns(2)
                ->schema([
                    Flex::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Intitulé')
                            ->required()
                            ->readOnly(
                                fn (?string $operation = null) => $operation === 'edit' && ! auth()->user()->hasRole(
                                    RoleEnum::ADMIN->value
                                )
                            )
                            ->maxLength(255),
                        Forms\Components\ToggleButtons::make('validated')
                            ->label('Validée')
                            ->options(YesOrNoEnum::class)
                            ->inline()
                            ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value))
                            ->grow(false),
                    ])
                        ->grow(true)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('operational_objective_id')
                        ->label('Objectif opérationnel')
                        ->relationship(
                            name: 'operationalObjective',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->orderBy('name', 'asc')
                        )
                        ->searchable(['name'])
                        ->disabled(
                            fn (?string $operation = null) => $operation === 'edit' && ! auth()->user()->hasRole(
                                RoleEnum::ADMIN->value
                            )
                        )
                        ->preload()
                        ->required()
                        ->visible(fn () => $owner === null)
                        ->columnSpanFull(),
                ]),
            Section::make('Progression')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('state')
                        ->label('État d\'avancement')
                        ->required()
                        ->options(ActionStateEnum::class)
                        ->suffixIcon('tabler-ladder'),
                    Forms\Components\TextInput::make('state_percentage')
                        ->label('Pourcentage')
                        ->suffixIcon('tabler-percentage')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(100),
                    Forms\Components\ToggleButtons::make('type')
                        ->label('Type')
                        ->default(ActionTypeEnum::PST->value)
                        ->options(ActionTypeEnum::class)
                        ->disabled(
                            fn (?string $operation = null) => $operation === 'edit' && ! auth()->user()->hasRole(
                                RoleEnum::ADMIN->value
                            )
                        )
                        ->inline(),
                ]),
            Section::make('Options')
                ->columns(4)
                ->schema([
                    Forms\Components\ToggleButtons::make('roadmap')
                        ->label('Feuille de route')
                        ->options(ActionRoadmapEnum::class)
                        ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value))
                        ->inline(),
                    Forms\Components\ToggleButtons::make('scope')
                        ->label('Volet')
                        ->options(ActionScopeEnum::class)
                        ->required()
                        ->inline(),
                    Forms\Components\ToggleButtons::make('synergy')
                        ->label(ActionSynergyEnum::getTitle())
                        ->helperText(ActionSynergyEnum::getDescription())
                        ->options(ActionSynergyEnum::class)
                        ->required()
                        ->inline(),
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Date d\'échéance')
                        ->suffixIcon('tabler-calendar-stats'),
                ]),
            Forms\Components\RichEditor::make('description'),
        ];
    }

    private static function fieldsTeam(): array
    {
        return [
            Fieldset::make('Mandataires et agents')
                ->schema([
                    Forms\Components\Select::make('action_mandatory')
                        ->label('Mandataires')
                        ->relationship(
                            name: 'mandataries',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->whereHas(
                                    'roles',
                                    fn (Builder $query) => $query->where('name', RoleEnum::MANDATAIRE->value)
                                )
                                ->orderBy('last_name')
                                ->orderBy('first_name'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Model $record) => "{$record->first_name} {$record->last_name}"
                        )
                        ->searchable(['first_name', 'last_name'])
                        ->multiple()
                        ->preload(),
                    Forms\Components\Select::make('action_users')
                        ->label('Agents pilotes')
                        ->helperText('Les agents pilotes ont le droit de modifier l\'action.')
                        ->relationship(
                            name: 'users',
                            modifyQueryUsing: fn (Builder $query) => $query->forSelectedDepartment()->orderBy('last_name')
                                ->orderBy('first_name'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Model $record) => "{$record->first_name} {$record->last_name}"
                        )
                        ->searchable(['first_name', 'last_name'])
                        ->multiple(),
                ])
                ->columns(3),
            Fieldset::make('Services porteurs et partenaires externes')
                ->schema([
                    Forms\Components\Select::make('action_service_leader')
                        ->label('Services porteurs')
                        ->helperText('L\'agent membre des services porteurs, peut modifier l\'action.')
                        ->relationship(name: 'leaderServices', titleAttribute: 'name')
                        ->preload()
                        ->multiple()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                        ]),
                    Forms\Components\Select::make('action_service_partner')
                        ->label('Services partenaires')
                        ->relationship(name: 'partnerServices', titleAttribute: 'name')
                        ->preload()
                        ->multiple()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                        ]),
                ])
                ->columns(2),
            Forms\Components\Select::make('partners')
                ->label('Partenaires externes')
                ->relationship(name: 'partners', titleAttribute: 'name')
                ->multiple()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required(),
                ]),
            Forms\Components\Select::make('action_related')
                ->label('Actions liés')
                ->relationship(
                    name: 'linkedActions',
                    titleAttribute: 'name',
                )
                ->searchable(['actions.id', 'actions.name'])
                ->getOptionLabelFromRecordUsing(
                    fn (Model $record) => "{$record->id}. {$record->name}"
                )
                ->multiple(),
        ];
    }

    private static function fieldsFinancing(): array
    {
        return [
            Forms\Components\Textarea::make('budget_estimate')
                ->label('Budget estimé'),

            Forms\Components\Textarea::make('financing_mode')
                ->label('Mode de financement'),
        ];
    }

    private static function fieldsDescription(): array
    {
        return [
            Forms\Components\Textarea::make('evaluation_indicator')
                ->label('Indicateur d\'évaluation'),
            Forms\Components\Textarea::make('work_plan')
                ->label('Plan de travail'),
        ];
    }

    private static function fieldsOdd(): array
    {
        return [
            Forms\Components\Select::make('odds')
                ->label('Odds')
                ->relationship(name: 'odds')
                ->getOptionLabelFromRecordUsing(
                    fn (Model $record) => "{$record->id}. {$record->name}"
                )
                ->multiple()
                ->preload(),
        ];
    }
}
