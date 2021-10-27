<?php

namespace App\Utils;

class AppUtils {

    public static function getAppName(): string {
        $appname = 'Clientarea';
        if(isset($_ENV['APP_NAME']) && !empty($_ENV['APP_NAME'])) $appname = $_ENV['APP_NAME'];
        return $appname;
    }
}