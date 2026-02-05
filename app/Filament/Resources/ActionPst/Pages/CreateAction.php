<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Events\ActionProcessed;
use App\Filament\Resources\ActionPst\ActionPstResource;
use App\Repository\UserRepository;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateAction extends CreateRecord
{
    protected static string $resource = ActionPstResource::class;

    protected static ?string $title = 'Ajouter une action';

    /**
     * to set department before save
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);
        if (
            self::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->department = UserRepository::departmentSelected();
        $record->save();

        return $record;
    }

    protected function afterCreate(): void
    {
        if ($this->record->validated === false) {
            ActionProcessed::dispatch($this->record);
        }
    }
}
