<?php

namespace App\Filament\Resources\Odd\Pages;

use App\Filament\Resources\Odd\OddResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditOdd extends EditRecord
{
    protected static string $resource = OddResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
