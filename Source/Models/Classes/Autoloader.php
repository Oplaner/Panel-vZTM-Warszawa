<?php

final class Autoloader {
    private const SOURCE_DIRECTORY = __DIR__."/../../";
    private const TESTS_DIRECTORY = __DIR__."/../../../Tests/";
    private const ROOT_FILE_NAME = "index.php";
    private const SELF_FILE_NAME = __CLASS__.".php";

    private static ?Autoloader $sharedInstance = null;

    private array $entityFiles;

    private function __construct(bool $withTestsDirectoryScanning) {
        $this->entityFiles = [];
        $this->scanDirectory(self::SOURCE_DIRECTORY);

        if ($withTestsDirectoryScanning) {
            $this->scanDirectory(self::TESTS_DIRECTORY);
        }

        spl_autoload_register(fn ($entityName) => $this->loadEntity($entityName));
    }

    public static function scanSourceDirectory(): void {
        self::initializeOnce(false);
    }

    public static function scanSourceAndTestsDirectory(): void {
        self::initializeOnce(true);
    }

    private static function initializeOnce(bool $withTestsDirectoryScanning): void {
        if (is_null(self::$sharedInstance)) {
            self::$sharedInstance = new Autoloader($withTestsDirectoryScanning);
        }
    }

    private function scanDirectory(string $directory): void {
        $elements = array_diff(
            scandir($directory),
            [".", "..", self::ROOT_FILE_NAME, self::SELF_FILE_NAME]
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