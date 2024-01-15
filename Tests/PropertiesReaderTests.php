<?php

require_once "../Source/Models/Classes/PropertiesReader.php";

final class PropertiesReaderTests {
    public static function throwExceptionWhenRequestingInvalidPropertiesGroup(): bool|string {
        try {
            $group = "invalidGroup";
            $properties = PropertiesReader::getProperties($group);
        } catch (Exception $exception) {
            return true;
        }

        return "PropertiesReader did not throw exception for invalid \"$group\" properties group.";
    }

    public static function checkPropertiesFileIsReadOnlyOnce(): bool|string {
        $group = "database";
        $fileBasePath = "../Source/Properties/";
        $old = $fileBasePath.$group.".properties";
        $new = $fileBasePath.$group."-new.properties";
        $firstFetchResult = PropertiesReader::getProperties($group);
        rename($old, $new);
        $secondFetchResult = PropertiesReader::getProperties($group);
        rename($new, $old);

        if ($firstFetchResult != $secondFetchResult) {
            return "Properties of the same group have not been cached.";
        } else {
            return true;
        }
    }

    public static function checkDatabasePropertiesAreLoadedCorrectly(): bool|string {
        $properties = PropertiesReader::getProperties("database");

        if (!isset($properties["hostname"], $properties["username"], $properties["password"], $properties["database"])) {
            return "Loaded database properties are incorrect. Data found: ".trim(print_r($properties, true)).".";
        } else {
            return true;
        }
    }
}