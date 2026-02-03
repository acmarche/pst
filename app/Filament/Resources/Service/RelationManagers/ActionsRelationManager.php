<?php

namespace App\Filament\Resources\Service\RelationManagers;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Models\Action;
use App\Models\Service;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'leadingActions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        /** @var Service $ownerRecord */
        return $ownerRecord->actionsForDepartment()->count().' Actions liées';
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
        /** @var Service $service */
        $service = $this->ownerRecord;

        return $service->actionsForDepartment();
    }
}
