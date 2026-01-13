<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partner\Pages;

use App\Filament\Resources\ActionPst\ActionPstResource;
use App\Filament\Resources\Partner\PartnerResource;
use App\Models\Action;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

final class ViewPartner extends ViewRecord
{
    protected static string $resource = PartnerResource::class;

    public function getTitle(): string
    {
        return $this->record->name.' '.$this->record->initials ?? 'Empty name';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('email')
                    ->icon('tabler-mail'),
                TextEntry::make('phone')
                    ->icon('tabler-phone'),
                TextEntry::make('description')
                    ->label(null)
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                Fieldset::make('actions')
                    ->label('Actions liés')
                    ->schema([
                        RepeatableEntry::make('actions')
                            ->label(null)
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nom')
                                    ->columnSpanFull()
                                    ->url(
                                        fn (Action $record): string => ActionPstResource::getUrl(
                                            'view',
                                            ['record' => $record]
                                        )
                                    ),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
