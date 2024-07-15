<?php

final class Autoloader {
    public const SOURCE_DIRECTORY = __DIR__."/../../";

    private static ?Autoloader $sharedInstance = null;

    private string $rootFile;
    private array $entityFiles;

    private function __construct(string $rootFile) {
        $this->rootFile = $rootFile;
        $this->entityFiles = [];
        $this->scanDirectory(self::SOURCE_DIRECTORY);
        spl_autoload_register(fn ($entityName) => $this->loadEntity($entityName));
    }

    public static function scanSourceDirectory(string $rootFile): void {
        if (is_null(self::$sharedInstance)) {
            self::$sharedInstance = new Autoloader($rootFile);
        }
    }

    private function scanDirectory(string $directory): void {
        $elements = array_diff(
            scandir($directory),
            [".", "..", $this->rootFile, __CLASS__.".php"]
        );

        foreach ($elements as $element) {
            if (is_dir($directory.$element)) {
                $this->scanDirectory($directory.$element."/");
            } elseif (preg_match("/^\S+\.php$/", $element)) {
                $entityName = preg_replace("/^(\S+)\.php$/", "$1", $element);
                $entityPath = $directory.$element;
                $this->entityFiles[$entityName] = $entityPath;
            }
        }
    }

    private function loadEntity(string $entityName): void {
        require_once $this->entityFiles[$entityName];
    }
}

?>