<?php

namespace App\Filament\Resources\Odd;

use App\Filament\Resources\Odd\Schemas\OddForm;
use App\Filament\Resources\Odd\Tables\OddTables;
use App\Models\Odd;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

// todo try https://github.com/LaravelDaily/FilamentExamples-Projects/tree/main/tables/table-reorderable-position
final class OddResource extends Resource
{
    protected static ?string $model = Odd::class;

    protected static string|null|BackedEnum $navigationIcon = 'tabler-trees';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Développement durable (ODD)';

    public static function getModelLabel(): string
    {
        return 'Objectif de développement durable (ODD)';
    }

    public static function form(Schema $schema): Schema
    {
        return OddForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OddTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOdds::route('/'),
            'create' => Pages\CreateOdd::route('/create'),
            'view' => Pages\ViewOdd::route('/{record}'),
            'edit' => Pages\EditOdd::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }
}
