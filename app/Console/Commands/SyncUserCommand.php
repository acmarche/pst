<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Constant\RoleEnum;
use App\Ldap\User as UserLdap;
use App\Models\Role;
use App\Models\User;
use App\Models\UserIntranet;
use Illuminate\Console\Command;
use Str;
use Symfony\Component\Console\Command\Command as SfCommand;

final class SyncUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pst:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users with ldap';

    private Role $agentRole;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // $this->agentRole = Role::where('name', RoleEnum::AGENT->value)->first();

        foreach (UserLdap::all() as $userLdap) {
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! $this->isActif($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            if (! $user = User::where('username', $username)->first()) {
                //  $this->addUser($username, $userLdap);
            } else {
                $this->updateUser($user, $userLdap);
            }
        }

        //  $this->removeOldUsers();

        return SfCommand::SUCCESS;
    }

    private function addUser(string $username, UserLdap $userLdap): void
    {
        $data = $this->data($userLdap, $username);
        $data['username'] = $username;
        $data['password'] = Str::password();
        $user = User::create($data);
        $user->addRole($this->agentRole);
        $this->info('Add '.$user->first_name.' '.$user->last_name);
    }

    private function updateUser(User $user, mixed $userLdap): void
    {
        $user->update(User::generateDataFromLdap($userLdap, $user->username));
        $this->info('Update '.$user->first_name.' '.$user->last_name);
    }

    private function removeOldUsers(): void
    {
        $ldapUsernames = array_map(function (UserLdap $userLdap) {
            return $userLdap->getFirstAttribute('samaccountname');
        }, UserLdap::all()->toArray());
        foreach (User::all() as $user) {
            if (in_array($user->username, $ldapUsernames)) {
                // $user->delete();
                $this->info('Removed '.$user->first_name.' '.$user->last_name);
            }
        }
    }

    private function isActif(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
