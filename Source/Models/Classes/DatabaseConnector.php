<?php

require_once __DIR__."/Logger.php";
require_once __DIR__."/PropertiesReader.php";
require_once __DIR__."/../Enums/LogLevel.php";

final class DatabaseConnector {
    private static ?mysqli $sharedInstance = null;

    public static function shared(): mysqli {
        if (is_null(self::$sharedInstance)) {
            self::$sharedInstance = self::initializeClient();
        }

        return self::$sharedInstance;
    }

    public static function closeConnection(): void {
        if (!is_null(self::$sharedInstance)) {
            self::$sharedInstance->close();
            self::$sharedInstance = null;
        }
    }

    private static function initializeClient(): mysqli {
        $properties = PropertiesReader::getProperties("database");
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

        try {
            $client = @new mysqli(
                "p:".$properties["hostname"],
                $properties["username"],
                $properties["password"],
                $properties["database"]
            );
        } catch (Exception $exception) {
            Logger::log(LogLevel::error, "Failed to connect to the database: \"".$exception->getMessage()."\".");
            throw new Exception("Failed to connect to the database.");
        }

        $client->set_charset($properties["charset"]);
        $client->query("SET collation_connection = '".$properties["collation"]."'");

        return $client;
    }
}

?>