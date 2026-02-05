<?php

namespace App\Filament\Resources\StrategicObjective\Pages;

use App\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use App\Repository\UserRepository;
use Filament\Resources\Pages\CreateRecord;

final class CreateStrategicObjective extends CreateRecord
{
    protected static string $resource = StrategicObjectiveResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['department'] = UserRepository::departmentSelected();

        return $data;
    }
}
