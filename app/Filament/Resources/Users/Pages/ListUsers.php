<?php

namespace App\Filament\Resources\Users\Pages;

use App\Constant\DepartmentEnum;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Ldap\User as UserLdap;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
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
                ->schema(fn(Schema $schema) => UserForm::add($schema))
                ->action(function (array $data) {
                    $username = $data['username'];
                    if (!$user = User::where('username', $username)->first()) {
                        if ($userLdap = UserLdap::query()->findBy('sAMAccountName', $username)) {
                            $dataUser = User::generateDataFromLdap($userLdap, $username);
                            $dataUser['username'] = $username;
                            $dataUser['password'] = Str::password();
                            $dataUser['departments'] = DepartmentEnum::from($data['departments'][0])->value;
                            $user = User::create($dataUser);
                            Notification::make()
                                ->success()
                                ->title('Utilisateur ajouté')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Utilisateur introuvable dans la LDAP')
                                ->send();
                        }
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Utilisateur déjà existant')
                            ->send();
                    }
                    if ($user) {
                        $this->redirect(UserResource::getUrl('view', ['record' => $user]));
                    }
                }),
            Actions\Action::make('roles_help')
                ->label('Les rôles')
                ->icon('tabler-user-heart')
                ->modal()
                ->modalHeading('Explications des différents rôles')
                ->modalContent(view('filament.resources.user-resource.pages.roles-help')),
        ];
    }
}
