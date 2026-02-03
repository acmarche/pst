<?php

namespace App\Filament\Resources\Service\RelationManagers;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Models\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'leadingActions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ' Actions';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ActionTables::actionsInline($table)
            ->modifyQueryUsing(fn (): Builder => $this->getActionsQuery());
    }

    /**
     * @return Builder<Action>
     */
    private function getActionsQuery(): Builder
    {
        $serviceId = $this->ownerRecord->getKey();

        /** @var Builder<Action> $query */
        $query = Action::query()->forSelectedDepartment();

        return $query->where(function (Builder $q) use ($serviceId): void {
            $q->whereHas('leaderServices', fn (Builder $q) => $q->where('services.id', $serviceId))
                ->orWhereHas('partnerServices', fn (Builder $q) => $q->where('services.id', $serviceId));
        });
    }
}
