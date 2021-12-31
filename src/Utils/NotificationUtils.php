<?php

namespace App\Utils;

use App\Entity\Notification;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

class NotificationUtils {

    public static function create(string $content, string $type, ManagerRegistry $doctrine, DateTimeImmutable $created_at = null): void {
        if(is_null($created_at)) $created_at = new DateTimeImmutable();
        $notification = (new Notification())
                        ->setContent($content)
                        ->setType($type)
                        ->setCreatedAt($created_at);
        
        $em = $doctrine->getManager();
        $em->persist($notification);
        $em->flush();
    }
}