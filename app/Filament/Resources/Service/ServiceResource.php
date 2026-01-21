<?php

namespace App\Filament\Resources\Service;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Service\Schemas\ServiceForm;
use App\Filament\Resources\Service\Tables\ServiceTables;
use App\Models\Service;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

final class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|null|BackedEnum $navigationIcon = 'tabler-users-group';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?bool $shouldSplitGlobalSearchTerms = false;

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

    /**
     * @return array<string, string|null>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Initiales' => $record->initials,
        ];
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(Service::search($search)->keys())
            ->limit(self::$globalSearchResultsLimit)
            ->get()
            ->map(function (Model $record): ?GlobalSearchResult {
                $url = self::getGlobalSearchResultUrl($record);

                if (blank($url)) {
                    return null;
                }

                return new GlobalSearchResult(
                    title: self::getGlobalSearchResultTitle($record),
                    url: $url,
                    details: self::getGlobalSearchResultDetails($record),
                    actions: self::getGlobalSearchResultActions($record),
                );
            })
            ->filter();
    }
}
