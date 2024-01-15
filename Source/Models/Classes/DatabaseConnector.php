<?php

require_once __DIR__."/PropertiesReader.php";

final class DatabaseConnector {
    private static ?mysqli $sharedInstance = null;

    public static function shared(): mysqli {
        if (is_null(self::$sharedInstance)) {
            self::$sharedInstance = self::initializeClient();
        }

        return self::$sharedInstance;
    }

    private static function initializeClient(): mysqli {
        $credentials = PropertiesReader::getProperties("database");
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

        try {
            $client = @new mysqli(
                "p:".$credentials["hostname"],
                $credentials["username"],
                $credentials["password"],
                $credentials["database"]
            );
        } catch (Exception $exception) {
            throw new Exception("Failed to connect to the database.");
        }

        // TODO: Set charset and collation.

        return $client;
    }
}

?>