<?php

namespace App\Filament\Resources\ActionPst\Schemas;

use App\Constant\ActionRoadmapEnum;
use App\Constant\ActionStateEnum;
use App\Constant\ActionSynergyEnum;
use App\Constant\ActionTypeEnum;
use App\Constant\RoleEnum;
use App\Models\OperationalObjective;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ActionForm
{
    public static function createForm(Schema $schema, Model|OperationalObjective|null $owner): Schema
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
                        ->schema(
                            self::fieldsTeam(),
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
                    ->submitAction(view('components.btn_add', ['label' => 'Ajouter l\'action'])),
            ]);
    }

    public static function fieldsAttachment(): array
    {
        return
            [
                Forms\Components\Hidden::make('file_mime'),
                Forms\Components\Hidden::make('file_size'),
                Forms\Components\TextInput::make('file_name')
                    ->label('Nom du média')
                    ->required()
                    ->maxLength(150),
                FileUpload::make('media')
                    ->label('Pièce jointe')
                    ->required()
                    ->maxFiles(1)
                    ->disk('public')
                    ->directory('uploads')
                    // ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    // ->preserveFilenames()
                    ->downloadable()
                    ->maxSize(10240)
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state instanceof TemporaryUploadedFile) {
                            $set('file_mime', $state->getMimeType());
                            $set('file_size', $state->getSize());
                        }
                    }),
            ];
    }

    public static function fieldsReminder(): array
    {
        return
            [
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
            Flex::make([
                Forms\Components\TextInput::make('name')
                    // ->prefix('CPAS') todo ?
                    ->label('Intitulé')
                    ->required()
                    ->maxLength(250),
                Forms\Components\ToggleButtons::make('to_validate')
                    ->label('Validée')
                    ->options([0 => 'Oui', 1 => 'Non'])
                    ->colors([
                        0 => 'success',
                        1 => 'warning',
                    ])
                    ->inline()
                    ->visible(fn () => auth()->user()->hasRole(RoleEnum::ADMIN->value))
                    ->grow(false),
            ])
                ->grow(true),
            Forms\Components\Select::make('operational_objective_id')
                ->label('Objectif opérationel')
                ->relationship(
                    name: 'operationalObjective',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('name', 'asc')
                )
                ->searchable(['name'])
                ->preload()
                ->required()
                ->visible(fn () => $owner === null),
            Grid::make(3)
                ->schema([
                    Forms\Components\Select::make('state')
                        ->label('Etat d\'avancement')
                        ->required()
                        ->options(ActionStateEnum::class)
                        ->suffixIcon('tabler-ladder'),
                    Forms\Components\TextInput::make('state_percentage')
                        ->label('Pourcentage d\'avancement')
                        ->suffixIcon('tabler-percentage')
                        ->integer()
                        ->maxWidth(Width::ExtraSmall),
                    Forms\Components\ToggleButtons::make('type')
                        ->label('Type')
                        ->default(ActionTypeEnum::PST->value)
                        ->options(ActionTypeEnum::class)
                        ->inline(),
                    Forms\Components\ToggleButtons::make('roadmap')
                        ->label('Feuille de route')
                        ->required(false)
                        ->options(ActionRoadmapEnum::class)
                        ->inline(),
                    Forms\Components\ToggleButtons::make('synergy')
                        ->label('Synergie CPAS / VILLLE')
                        ->required(false)
                        ->options(ActionSynergyEnum::class)
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
                        ->relationship(
                            name: 'users',
                            modifyQueryUsing: fn (Builder $query) => $query->orderBy('last_name')
                                ->orderBy('first_name'),
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Model $record) => "{$record->first_name} {$record->last_name}"
                        )
                        ->searchable(['first_name', 'last_name'])
                        ->multiple(),
                ])
                ->columns(3),
            Fieldset::make('Services porteurs et partenaires')
                ->schema([
                    Forms\Components\Select::make('action_service_leader')
                        ->label('Services porteurs')
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
                ->relationship(name: 'odds', titleAttribute: 'name')
                ->multiple()
                ->preload(),
        ];
    }
}
