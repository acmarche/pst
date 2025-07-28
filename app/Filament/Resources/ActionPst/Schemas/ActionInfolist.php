<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActionPst\Schemas;

use App\Constant\ActionStateEnum;
use App\Filament\Components\ProgressEntry;
use App\Models\Odd;
use App\Models\Partner;
use App\Models\Service;
use App\Models\User;
use DateTimeImmutable;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ActionInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([
                    Section::make('Informations')
                        ->label(null)
                        ->schema(self::informations())
                        ->grow(),
                    Section::make('Etat')
                        ->label(null)
                        ->schema(self::etat())
                        ->grow(false),
                ])->from('md')
                    ->columnSpanFull(),
            ]);
    }

    public static function informations(): array
    {
        return [
            TextEntry::make('description')
                ->label(null)
                ->html()
                ->prose()
                ->visible(fn ($state) => $state !== null && $state !== ''),
            Fieldset::make('team')
                ->label('Team')
                ->schema([
                    TextEntry::make('users')
                        ->label('Agents pilotes')
                        ->badge()
                        ->formatStateUsing(
                            fn (User $state): string => $state->last_name.' '.$state->first_name
                        ),
                    TextEntry::make('mandataries')
                        ->label('Mandataires')
                        ->badge()
                        ->formatStateUsing(
                            fn (User $state): string => $state->last_name.' '.$state->first_name
                        ),
                    TextEntry::make('leaderServices')
                        ->label('Services porteurs')
                        ->badge()
                        ->formatStateUsing(fn (Service $state): string => $state->name),
                    TextEntry::make('partnerServices')
                        ->label('Services partenaires')
                        ->badge()
                        ->formatStateUsing(fn (Service $state): string => $state->name),
                    TextEntry::make('partners')
                        ->label('Partenaires')
                        ->badge()
                        ->formatStateUsing(fn (Partner $state): string => $state->name),
                ]),
            self::odd(),
            self::budget(),
            TextEntry::make('work_plan')
                ->label('Plan de travail')
                ->html()
                ->prose(),
            TextEntry::make('evaluation_indicator')
                ->label('Indicateur d\'évaluation')
                ->html()
                ->prose(),
        ];
    }

    public static function etat(): array
    {
        return [
            TextEntry::make('state')
                ->label('Etat d\'avancement')
                ->formatStateUsing(fn (ActionStateEnum $state) => $state->getLabel())
                ->icon(fn (ActionStateEnum $state) => $state->getIcon())
                ->color(fn (ActionStateEnum $state) => $state->getColor()),
            ProgressEntry::make('state_percentage')
                ->label('Pourcentage d\'avancement'),
            TextEntry::make('due_date')
                ->label('Date d\'échéance')
                ->visible(fn (?DateTimeImmutable $date) => $date instanceof DateTimeImmutable)
                ->dateTime(),
            TextEntry::make('created_at')
                ->label('Créé le')
                ->dateTime(),
            TextEntry::make('user_add')
                ->label('Créé par'),
            TextEntry::make('department')
                ->label('Département'),
        ];
    }

    public static function odd(): Component
    {
        return
            Fieldset::make('odd_tab')
                ->label('Objectifs de développement durable')
                ->schema([
                    TextEntry::make('odds')
                        ->label(null)
                        ->formatStateUsing(fn (Odd $state): string => $state->name)
                        ->color('secondary')
                        ->badge(),
                ]);
    }

    public static function budget(): Component
    {
        return Fieldset::make('budget')
            ->label('Financement')
            ->schema([
                TextEntry::make('budget_estimate')
                    ->markdown()
                    ->label('Budget estimé')
                    ->prose(),
                TextEntry::make('financing_mode')
                    ->markdown()
                    ->label('Mode de financement')
                    ->prose(),
            ]);

    }
}
