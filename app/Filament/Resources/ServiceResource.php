<?php

namespace App\Filament\Resources;

use App\Constant\NavigationGroupEnum;
use App\Filament\Resources\Service\Pages;
use App\Filament\Resources\Service\Schemas\ServiceForm;
use App\Filament\Resources\Service\Tables\ServiceTables;
use App\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|null|\BackedEnum $navigationIcon = 'tabler-users-group';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroupEnum::Settings->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
