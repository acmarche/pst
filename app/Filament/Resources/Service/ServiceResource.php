<?php

namespace App\Filament\Resources\Service;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Service\Schemas\ServiceForm;
use App\Filament\Resources\Service\Tables\ServiceTables;
use App\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|null|\BackedEnum $navigationIcon = 'tabler-users-group';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

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
