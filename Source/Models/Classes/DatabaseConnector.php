<?php

final class DatabaseConnector {
    private static ?mysqli $sharedInstance = null;

    public function __construct() {}

    public static function shared(): mysqli {
        if (is_null(self::$sharedInstance)) {
            self::$sharedInstance = self::initializeClient();
        }

        return self::$sharedInstance;
    }

    public static function closeConnection(): void {
        if (isset(self::$sharedInstance)) {
            self::$sharedInstance->close();
            self::$sharedInstance = null;
            Logger::log(LogLevel::info, "Closed database connection.");
        }
    }

    private static function initializeClient(): mysqli {
        $properties = PropertiesReader::getProperties("database");
        $client = new mysqli(
            "p:".$properties["hostname"],
            $properties["username"],
            $properties["password"],
            $properties["database"]
        );
        $client->set_charset($properties["charset"]);
        $client->query("SET collation_connection = '".$properties["collation"]."'");
        Logger::log(LogLevel::info, "Initialized database connection.");
        return $client;
    }
}

?>