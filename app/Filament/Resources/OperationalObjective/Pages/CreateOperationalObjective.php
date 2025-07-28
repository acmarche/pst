<?php

namespace App\Filament\Resources\OperationalObjective\Pages;

use App\Filament\Resources\OperationalObjectiveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOperationalObjective extends CreateRecord
{
    protected static string $resource = OperationalObjectiveResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Nouvel objectif Opérationnel (Oo)';
    }
}
