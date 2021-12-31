<?php

namespace App\Utils;

use App\Entity\Hosts;
use Doctrine\Persistence\ManagerRegistry;

class HostUtils {

    public static function getHost($id, ManagerRegistry $doctrine): ?Hosts{
        $host = $doctrine->getRepository(Hosts::class)->find($id);
        if(!empty($host)) return $host;
        return null;
    }

    public static function exists($id, ManagerRegistry $doctrine): bool {
        $host = $doctrine->getRepository(Hosts::class)->find($id);
        if(!empty($host)) return true;
        return false;
    }

    public static function canAccessToHost($hostId, $userId, ManagerRegistry $doctrine, $adminId = null): bool {
        if(!self::exists($hostId, $doctrine)) return false;
        $host = $doctrine->getRepository(Hosts::class)->findBy(['author_id' => $userId]);
        if(!empty($host)) return true;
        else {
            if(!is_null($adminId)){
                if(AdminUtils::adminExists($adminId, $doctrine)){
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }
    }

    public static function isSuspend($id, ManagerRegistry $doctrine): bool {
        return self::getHost($id, $doctrine)->getIsSuspend();
    }
}