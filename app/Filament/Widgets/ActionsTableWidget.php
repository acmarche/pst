<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActionResource\Tables\ActionTables;
use App\Repository\ActionRepository;
use Filament\Tables\Table;

//todo extends BaseWidget
class ActionsTableWidget
{
    public function table(Table $table): Table
    {
        $user = auth()->user();
        $table
            ->description('Actions liés à votre nom ou service')
            ->query(
                ActionRepository::findByUser($user->id)
            );

        return ActionTables::actionsInline($table, limit: 60);
    }
}
