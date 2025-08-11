<?php

namespace App\Repository;

use App\Constant\DepartmentEnum;
use App\Ldap\User as UserLdap;

final class UserRepository
{
    public static string $department_selected_key = 'department_selected';

    public static function departmentSelected(): string
    {
        $department = session(self::$department_selected_key, null);
        if (! $department) {
            if (auth()->user()) {
                if (count(auth()->user()->departments) > 0) {
                    return auth()->user()->departments[0];
                }
            }
        }

        return DepartmentEnum::VILLE->value;
    }

    public static function listUsersFromLdap(): array
    {
        $users = [];
        foreach (UserLdap::all() as $userLdap) {
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! self::isActif($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            $users[$username] = $userLdap;
        }

        usort($users, function (UserLdap $a, UserLdap $b) {
            return strcasecmp($a->getFirstAttribute('sn'), $b->getFirstAttribute('sn'));
        });

        return $users;
    }

    public static function listUsersFromLdapForSelect(): array
    {
        $users = [];
        foreach (self::listUsersFromLdap() as $userLdap) {
            $users[$userLdap->getFirstAttribute('samaccountname')] = $userLdap->getFirstAttribute(
                'sn'
            ).' '.$userLdap->getFirstAttribute('givenname');
        }

        return $users;
    }

    private static function isActif(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
