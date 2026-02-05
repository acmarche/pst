<?php

namespace App\Filament\Resources\OperationalObjective\Pages;

use App\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use App\Repository\UserRepository;
use Filament\Resources\Pages\CreateRecord;

final class CreateOperationalObjective extends CreateRecord
{
    protected static string $resource = OperationalObjectiveResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Nouvel objectif Opérationnel (Oo)';
    }

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
