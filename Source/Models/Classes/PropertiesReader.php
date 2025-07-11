<?php

final class PropertiesReader {
    private const PROPERTIES_DIRECTORY = __DIR__."/../../Properties/";
    private const PROPERTY_LINE_PATTERN = "/^(?<key>\S+)\s*=\s*(?:\"(?<stringValue>.*)\"|(?<nonStringValue>.+))$/";

    /*
        Only property groups listed below will be accepted during get request.
        The class will read a file only once and store the result in dedicated property.
    */
    private static ?array $applicationProperties = null;
    private static ?array $authenticatorProperties = null;
    private static ?array $databaseProperties = null;
    private static ?array $loggerProperties = null;

    public static function getProperties(string $group): array {
        $propertiesVariableName = $group."Properties";

        if (!in_array($propertiesVariableName, array_keys(get_class_vars(self::class)))) {
            throw new Exception("Requested properties group \"$group\" does not exist.");
        }

        if (is_null(self::$$propertiesVariableName)) {
            self::$$propertiesVariableName = self::readProperties($group);
        }

        return self::$$propertiesVariableName;
    }

    private static function readProperties(string $group): array {
        $path = self::PROPERTIES_DIRECTORY.$group.".properties";
        $file = fopen($path, "r");

        if ($file === false) {
            throw new Exception("Failed to open \"$group.properties\" file.");
        }

        if (!flock($file, LOCK_SH)) {
            fclose($file);
            throw new Exception("Failed to lock \"$group.properties\" file.");
        }

        $properties = [];

        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            $matches = [];

            if (preg_match(self::PROPERTY_LINE_PATTERN, $line, $matches, PREG_UNMATCHED_AS_NULL)) {
                $matches = array_values(
                    array_filter(
                        $matches,
                        fn($value, $key) => is_string($key) && isset($value),
                        ARRAY_FILTER_USE_BOTH
                    )
                );

                $key = $matches[0];
                $value = $matches[1];

                if (is_numeric($value)) {
                    $value += 0; // Cast value to a concrete numeric type (int|float).
                } elseif ($value == "false") {
                    $value = false;
                } elseif ($value == "true") {
                    $value = true;
                }

                $properties[$key] = $value;
            }
        }

        flock($file, LOCK_UN);
        fclose($file);

        return $properties;
    }
}

?>