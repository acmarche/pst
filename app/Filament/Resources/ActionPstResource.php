<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActionPst\Pages;
use App\Filament\Resources\ActionPst\RelationManagers\FollowUpsRelationManager;
use App\Filament\Resources\ActionPst\RelationManagers\HistoriesRelationManager;
use App\Filament\Resources\ActionPst\RelationManagers\MediasRelationManager;
use App\Filament\Resources\ActionPst\Schemas\ActionForm;
use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Models\Action;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

//https://www.youtube.com/watch?v=85uRvsUvwJQ&list=PLqDySLfPKRn6fgrrdg4_SmsSxWzVlUQJo&index=23
//https://filamentphp.com/content/leandrocfe-navigating-filament-pages-with-previous-and-next-buttons
class ActionPstResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static string|null|\BackedEnum $navigationIcon = 'tabler-bolt';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Liste des actions';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return ActionForm::createForm($schema, null);
    }

    public static function table(Table $table): Table
    {
        return ActionTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('group', [
                MediasRelationManager::class,
                FollowUpsRelationManager::class,
                HistoriesRelationManager::class,
            ]),

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActions::route('/'),
            'create' => Pages\CreateAction::route('/create'),
            'view' => Pages\ViewAction::route('/{record}'),
            'edit' => Pages\EditAction::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return ActionPstResource::getUrl('view', ['record' => $record]);
    }
}
