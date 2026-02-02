<?php

namespace App\Filament\Resources\OperationalObjective\Pages;

use App\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use App\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateOperationalObjective extends CreateRecord
{
    protected static string $resource = OperationalObjectiveResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        Notification::make()
            ->warning()
            ->title('Création non autorisée')
            ->body('Un objectif opérationnel doit être créé depuis la page de visualisation d\'un objectif stratégique.')
            ->persistent()
            ->send();

        $this->redirect(StrategicObjectiveResource::getUrl('index'));
    }

    public function getTitle(): string
    {
        return 'Nouvel objectif Opérationnel (Oo)';
    }
}
