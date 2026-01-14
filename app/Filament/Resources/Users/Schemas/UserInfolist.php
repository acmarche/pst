<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('email')
                ->label('Email')
                ->icon('tabler-mail'),
            TextEntry::make('phone')
                ->label('Téléphone')
                ->icon('tabler-phone'),
            TextEntry::make('mobile')
                ->label('Mobile')
                ->icon('tabler-device-mobile'),
            TextEntry::make('extension')
                ->label('Extension')
                ->icon('tabler-device-landline-phone'),
            TextEntry::make('departments')
                ->label('Départements')
                ->icon('tabler-device-mobile'),
            TextEntry::make('roles.label')
                ->label('Rôles')
                ->icon('tabler-user-shield'),
            TextEntry::make('services.name')
                ->label('Services')
                ->icon('tabler-users-group'),
        ]);

    }
}
