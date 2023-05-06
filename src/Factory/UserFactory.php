<?php

namespace App\Factory;

use App\Entity\User;

class UserFactory
{
    public static function createUser(string $locale): User
    {
        $user = new User();
        if($locale === 'cs_CZ')
        {
           $user->setDefaultCurrency('CZK');
           $user->setPreferredLocale('cs');
        }
        else {
            $user->setDefaultCurrency('EUR');
            $user->setPreferredLocale('en');
        }
        return $user;
    }
}