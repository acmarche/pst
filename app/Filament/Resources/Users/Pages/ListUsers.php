<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Ldap\UserHandler;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' agents';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ImportUser')
                ->label('Ajouter un utilisateur')
                ->icon('tabler-user-plus')
                ->modal()
                ->modalHeading('Importer un utilisateur de la LDAP')
                ->schema(fn (Schema $schema) => UserForm::add($schema))
                ->action(function (array $data) {
                    try {
                        $user = UserHandler::createUserFromLdap($data);
                        Notification::make()
                            ->success()
                            ->title('Utilisateur ajouté')
                            ->send();
                        if ($user) {
                            $this->redirect(UserResource::getUrl('view', ['record' => $user]));
                        }
                    } catch (Exception $exception) {
                        Notification::make()
                            ->danger()
                            ->title($exception->getMessage())
                            ->send();
                    }
                }),
            Actions\Action::make('roles_help')
                ->label('Rappel des rôles')
                ->icon('tabler-user-heart')
                ->modal()
                ->modalHeading('Explications des différents rôles')
                ->modalContent(view('filament.resources.user-resource.pages.roles-help')),
        ];
    }
}
