<?php

namespace App\Service;

use PDO;

class DB {
    private static ?PDO $instance = null;

    public static function conn(): PDO {
        if (!static::$instance) {
            $db_path = getenv('SQLITE_DB_PATH');
            static::$instance = new PDO("sqlite:{$db_path}");
            static::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return static::$instance;
    }
}
