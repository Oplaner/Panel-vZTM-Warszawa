<?php

final class PropertiesReader {
    private const PROPERTIES_DIRECTORY = __DIR__."/../../Properties/";
    private const PROPERTY_LINE_PATTERN = "/^(\S+)\s*=\s*(.+)$/";

    /*
        Only property groups listed below will be accepted during get request.
        The class will read a file only once and store the result in dedicated property.
    */
    private static ?array $databaseProperties = null;

    public static function getProperties(string $group): array {
        $propertiesVariableName = $group."Properties";

        if (!in_array($propertiesVariableName, array_keys(get_class_vars(self::class)))) {
            throw new Exception("Requested properties group \"$group\" does not exist.");
        }

        if (self::$$propertiesVariableName === null) {
            self::$$propertiesVariableName = self::readProperties($group);
        }

        return self::$$propertiesVariableName;
    }

    private static function readProperties(string $group): array {
        $path = self::PROPERTIES_DIRECTORY.$group.".properties";
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new Exception("Failed to read \"$group.properties\" file.");
        }

        $properties = [];

        foreach (explode(PHP_EOL, $contents) as $line) {
            $matches = [];

            if (preg_match(self::PROPERTY_LINE_PATTERN, $line, $matches)) {
                $properties[$matches[1]] = $matches[2];
            }
        }

        return $properties;
    }
}

?>