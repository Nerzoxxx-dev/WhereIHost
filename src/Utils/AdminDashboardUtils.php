<?php

namespace App\Utils;

use App\Entity\Admins;
use App\Entity\Hosts;
use App\Entity\Notification;
use App\Entity\Opinion;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class AdminDashboardUtils {

    public static function getDatas(ManagerRegistry $doctrine, $session): array {
        $hosts = $doctrine->getRepository(Hosts::class);
        $opinions = $doctrine->getRepository(Opinion::class);
        $users = $doctrine->getRepository(User::class);
        $admins = $doctrine->getRepository(Admins::class);
        $fiveHosts = $hosts->findBy([], ['likes' => "DESC"], 5);
        $opinionsFiveHostsNumber = [];
        $hostsA = [];
        $notificationsSQL = $doctrine->getRepository(Notification::class)->findBy([], ['created_at' => "DESC"], 9);
        $notifications = [];
        $firstNotification = null;
        $allHosts = [];

        if(!empty($notificationsSQL)){
            foreach($notificationsSQL as $n) {
                $notifications[] = [
                    $n->getContent(),
                    $n->getType(),
                    $n->getCreatedAt()->format('l dS F Y H:m:s'),
                    $n->getId()
                ];
            }


            $firstNotification = $notifications[0];
            unset($notifications[0]);
        }

        foreach($fiveHosts as $host){
            $opinionsFiveHostsNumber = count($opinions->findBy(['host_id' => $host->getId()]));
            $fiveHostsOpinions = $opinions->findBy(['host_id' => $host->getId()]);
            $note = 0;

            if($opinionsFiveHostsNumber > 0){
                foreach($fiveHostsOpinions as $opinion => $k){
                    $note = $note + $k->getNote();
                }
                
                $note = $note / count($fiveHostsOpinions);
                $note = $note / 5 * 100;
            }
            $hostsA[] = [
                "name" => $host->getName(),
                "note" => $note,
                "opinions_number" => $opinionsFiveHostsNumber,
            ];
        }
        if(!empty($hosts->findAll())){
            foreach($hosts->findBy([], ['name' => 'ASC']) as $host) {
                $note = 0;
                foreach($opinions->findBy(['host_id' => $host->getId()]) as $opinion){
                    $note += $opinion->getNote();
                }
                if(count($opinions->findBy(['host_id' => $host->getId()])) > 0){
                    $note = $note / count($opinions->findBy(['host_id' => $host->getId()]));
                }
                $note = $note / 5 * 100; 
                $allhosts[] = [
                    "name" => $host->getName(),
                    "website" => $host->getWebsite(),
                    "opinions_number" => count($opinions->findBy(['host_id' => $host->getId()])),
                    "note" => $note,
                    "id" => $host->getId(),
                    "isVerified" => $host->getIsVerified(),
                    "logoFilename" => $host->getLogoFilename()
                ];
            }
        }else {
            $allhosts = [];
        }

        if($session->has('searchHosts') && !is_null($session->get('searchHosts'))){
            $allhosts = [];
            $search = $session->get('searchHosts');
            if(!empty($search)){
                foreach($search as $host) {
                    $note = 0;
                    foreach($opinions->findBy(['host_id' => $host->getId()]) as $opinion){
                        $note += $opinion->getNote();
                    }
                    if(count($opinions->findBy(['host_id' => $host->getId()])) > 0){
                        $note = $note / count($opinions->findBy(['host_id' => $host->getId()]));
                    }
                    $note = $note / 5 * 100; 
                    $allhosts[] = [
                        "name" => $host->getName(),
                        "website" => $host->getWebsite(),
                        "opinions_number" => count($opinions->findBy(['host_id' => $host->getId()])),
                        "note" => $note,
                        "id" => $host->getId(),
                        "isVerified" => $host->getIsVerified(),
                        "logoFilename" => $host->getLogoFilename()
                    ];
                }
            }
            $session->remove('searchHosts');
        }
        return [
            "hostsCount" => count($hosts->findAll()),
            "opinionsCount" => count($opinions->findAll()),
            "usersCount" => count($users->findAll()),
            "adminsCount" => count($admins->findAll()),
            "hostsVerifiedCount" => count($hosts->findBy(["is_verified" => true])),
            "opinionsVerifiedCount" => count($opinions->findBy(["is_verified" => true])),
            "adminsGrantCount" => count($admins->findBy(["permissions" => "[ALL]"])),
            "fiveHosts" => $hostsA,
            "firstNotification" => $firstNotification,
            "notifications" => $notifications,
            "session" => $session,
            "allHosts" => $allhosts
        ];
    }
}