<?php

namespace App\Utils;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class StringUtils {

    public static function random(int $size): string {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < $size; $i++)
        {
        $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
        return $chaineAleatoire;
    }

    public static function checkUserToken(string $string, ManagerRegistry $doctrine){
        $user = $doctrine->getRepository(User::class)->findBy(['token' => $string]);

        if($user) return self::checkUserToken(self::random(strlen($string)), $doctrine);
        return $string;
    }

}