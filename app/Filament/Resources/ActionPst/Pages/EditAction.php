<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Filament\Resources\ActionPst\ActionPstResource;
use App\Models\Service;
use App\Models\TracksHistoryTrait;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditAction extends EditRecord
{
    use TracksHistoryTrait;

    protected static string $resource = ActionPstResource::class;

    /**
     * @var array<string, array<int>>
     */
    private array $oldRelationshipIds = [];

    /**
     * to remove word "editer"
     */
    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    /**
     * Hide relation managers on Edit page - they are shown on View page only.
     */
    protected function getAllRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        $this->oldRelationshipIds = [
            'users' => $record->users()->pluck('users.id')->toArray(),
            'leaderServices' => $record->leaderServices()->pluck('services.id')->toArray(),
            'partnerServices' => $record->partnerServices()->pluck('services.id')->toArray(),
            'mandataries' => $record->mandataries()->pluck('users.id')->toArray(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        $relationships = [
            'users' => [
                'old' => $this->oldRelationshipIds['users'],
                'new' => $record->users()->pluck('users.id')->toArray(),
                'label' => 'agent pilote',
                'getDisplayName' => fn (int $id): string => $this->getUserDisplayName($id),
            ],
            'leaderServices' => [
                'old' => $this->oldRelationshipIds['leaderServices'],
                'new' => $record->leaderServices()->pluck('services.id')->toArray(),
                'label' => 'service porteur',
                'getDisplayName' => fn (int $id): string => $this->getServiceDisplayName($id),
            ],
            'partnerServices' => [
                'old' => $this->oldRelationshipIds['partnerServices'],
                'new' => $record->partnerServices()->pluck('services.id')->toArray(),
                'label' => 'service partenaire',
                'getDisplayName' => fn (int $id): string => $this->getServiceDisplayName($id),
            ],
            'mandataries' => [
                'old' => $this->oldRelationshipIds['mandataries'],
                'new' => $record->mandataries()->pluck('users.id')->toArray(),
                'label' => 'mandataire',
                'getDisplayName' => fn (int $id): string => $this->getUserDisplayName($id),
            ],
        ];

        $this->trackRelationships($record, $relationships);
    }

    private function getUserDisplayName(int $id): string
    {
        $user = User::find($id);

        return $user ? "{$user->first_name} {$user->last_name}" : "ID: {$id}";
    }

    private function getServiceDisplayName(int $id): string
    {
        $service = Service::find($id);

        return $service?->name ?? "ID: {$id}";
    }
}
