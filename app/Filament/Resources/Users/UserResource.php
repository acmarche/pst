<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Enums\NavigationGroupEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

    protected static ?string $recordTitleAttribute = 'fullName';

    public static function getModelLabel(): string
    {
        return 'Agents';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::getUser()->hasRole(RoleEnum::ADMIN->value);
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(User::search($search)->keys())
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
