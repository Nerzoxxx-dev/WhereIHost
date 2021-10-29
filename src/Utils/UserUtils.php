<?php

namespace App\Utils;

use App\Entity\User;
use App\Utils\Validator\Validator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserUtils {

    public static function userExists($id, ManagerRegistry $doctrine): bool {
        if(!is_int($id)) return false;

        $user = $doctrine->getRepository(User::class)->find($id);
        if(!$user) return false;
        return true;
    }

    public static function userExistsByEmail($email, ManagerRegistry $doctrine, TranslatorInterface $trans): bool {
        $errors = (new Validator())->email($email, $trans)->validate();

        if(!empty($errors)) return false;

        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if(!$user) return false;
        return true;
    }

    public static function getByEmail($email, ManagerRegistry $doctrine, TranslatorInterface $trans): ?User {
        $errors = (new Validator())->email($email, $trans)->validate();

        if(!empty($errors)) return null;

        return $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public static function userExistsByPasswordToken($token, ManagerRegistry $doctrine){
        $user = $doctrine->getRepository(User::class)->findOneBy(['password_token' => $token]);

        if(!$user) return false;
        return true;
    }

    public static function getByPasswordToken($token, ManagerRegistry $doctrine): ?User{
        $user = $doctrine->getRepository(User::class)->findOneBy(['password_token' => $token]);

        return $user;
    }
}