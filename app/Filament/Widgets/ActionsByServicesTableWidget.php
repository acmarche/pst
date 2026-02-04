<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Repository\ActionRepository;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class ActionsByServicesTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $table
            ->heading('Actions de vos services')
            ->description('Actions où vous êtes membre d\'un service porteur ou partenaire')
            ->query(
                ActionRepository::findByUserServices($user->id)
            );

        return ActionTables::actionsForWidget($table, limit: 60);
    }
}
