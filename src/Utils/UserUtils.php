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

    public static function userExistsByUsername($username, ManagerRegistry $doctrine, TranslatorInterface $trans): bool {
        $errors = (new Validator())->username($username, $trans)->validate();

        if(!empty($errors)) return false;

        $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $username]);

        if(!$user) return false;
        return true;
    }

    public static function userExistsByPhoneNumber($phone_number, ManagerRegistry $doctrine, TranslatorInterface $trans): bool {
        $errors = (new Validator())->phoneNumber($trans, $phone_number, 'phone_number')->validate();

        if(!empty($errors)) return false;

        $user = $doctrine->getRepository(User::class)->findOneBy(['phone_number' => $phone_number]);

        if(!$user) return false;
        return true;
    }

    public static function getById($id, ManagerRegistry $doctrine) :?User {
        $user = $doctrine->getRepository(User::class)->find($id);
        return $user;
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

    public static function getByUserName($username, ManagerRegistry $doctrine): ?User {
        return $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public static function isVerified($id, ManagerRegistry $doctrine): bool{
        $user = self::getById($id, $doctrine);

        if(!$user) return false;
        if($user->getVerifiedAt() === null) return false;
        return true;
    }

    public static function accountExists($request, ManagerRegistry $doctrine, TranslatorInterface $trans): bool {
        if(self::userExistsByEmail($request->get('email'), $doctrine, $trans)) return true;
        if(self::userExistsByUsername($request->get('username'), $doctrine, $trans)) return true;
        if(self::userExistsByPhoneNumber($request->get('phone_number'), $doctrine, $trans)) return true;
        return false;
    }
}