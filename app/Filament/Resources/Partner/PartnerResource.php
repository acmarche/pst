<?php

namespace App\Filament\Resources\Partner;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Partner\Schemas\PartnerForm;
use App\Filament\Resources\Partner\Tables\PartnerTables;
use App\Models\Partner;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Partenaires externes';
    }

    public static function getModelLabel(): string
    {
        return 'Partenaire externe';
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'view' => Pages\ViewPartner::route('/{record}'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(Partner::search($search)->keys())
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
