<?php

namespace App\Filament\Resources\OperationalObjective;

use App\Filament\Resources\OperationalObjective\Tables\OperationalObjectiveTables;
use App\Models\OperationalObjective;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class OperationalObjectiveResource extends Resource
{
    protected static ?string $model = OperationalObjective::class;

    protected static string|null|BackedEnum $navigationIcon = 'tabler-target';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Objectif Opérationnel (OO)';
    }

    public static function table(Table $table): Table
    {
        return OperationalObjectiveTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperationalObjectives::route('/'),
            'create' => Pages\CreateOperationalObjective::route('/create'),
            'view' => Pages\ViewOperationalObjective::route('/{record}'),
            'edit' => Pages\EditOperationalObjective::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(OperationalObjective::search($search)->keys())
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
