<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Repository\ActionRepository;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
//extends BaseWidget
class ActionsTableWidget
{
    public function table(Table $table): Table
    {
        $user = auth()->user();
        $table
            ->description('Actions vous concernant')
            ->query(
                ActionRepository::findByUser($user->id)
            );

        return ActionTables::actionsInline($table, limit: 60);
    }
}
