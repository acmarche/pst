<?php

namespace App\Auth;

use App\Ldap\User as UserLdap;
use App\Models\User;
use LdapRecord\Container;

class LdapAuthService
{
    /**
     * @param string $username
     * @param string $password
     * @return User|null
     * @throws \LdapRecord\Auth\PasswordRequiredException
     * @throws \LdapRecord\Auth\UsernameRequiredException
     * @throws \LdapRecord\ContainerException
     */
    public static function checkPassword(string $username, string $password): ?User
    {
        $user = User::where('username', '=', $username)->first();
        if (app()->environment('local')) {

            return $user;
        }
        if ($user) {
            $userLdap = UserLdap::where('sAMAccountName', '=', $user->username)->first();
            if (!$userLdap) {

                return null;
            }
            $connection = Container::getConnection('default');

            if ($connection->auth()->attempt($userLdap->getDn(), $password)) {
                return $user;
            } else {
                $message = $connection->getLdapConnection()->getDiagnosticMessage();

                if (strpos($message, '532') !== false) {
                    //"Your password has expired.";
                    return null;
                }
            }
        }

        return null;
    }
}
