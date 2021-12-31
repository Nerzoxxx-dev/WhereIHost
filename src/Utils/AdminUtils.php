<?php

namespace App\Utils;

use App\Entity\Admins;
use App\Utils\Validator\Validator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminUtils {

    public static function adminExists($id, ManagerRegistry $doctrine): bool {
        if(!is_int($id)) return false;

        $u = $doctrine->getRepository(Admins::class)->find($id);
        if(!$u) return false;
        return true;
    }

    public static function adminExistsByEmail(string $email, ManagerRegistry $doctrine, TranslatorInterface $trans): bool{
        $errors = (new Validator())->email($email, $trans)->validate();

        if(!empty($errors)) return false;

        $user = $doctrine->getRepository(Admins::class)->findBy(['email' => $email]);
        if(empty($user)) return false;
        return true;
    }

    public static function getAdminByEmail(string $email, ManagerRegistry $doctrine): Admins{
        return $doctrine->getRepository(Admins::class)->findBy(['email' => $email])[0];
    }

    public static function getAdmin($id, ManagerRegistry $doctrine): Admins {
        return $doctrine->getRepository(Admins::class)->find($id);
    }
}