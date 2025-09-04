<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Repository\ActionRepository;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActionsTableWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        $user = auth()->user();
        $table
            ->heading('Actions vous concernant')
            ->description('Vous êtes agent pilote')
            ->query(
                ActionRepository::findByUser($user->id)
            );

        return ActionTables::actionsInline($table, limit: 60);
    }
}
