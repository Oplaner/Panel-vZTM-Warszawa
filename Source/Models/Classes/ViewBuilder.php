<?php

final class ViewBuilder {
    private const VIEWS_DIRECTORY = __DIR__."/../../Views/";

    private static ?string $basePageTitle = null;

    public static function buildHead(Style $style, array $scripts, ?string $pageSubtitle): void {
        $basePageTitle = self::getBasePageTitle();
        $pageTitle = is_null($pageSubtitle) ? $basePageTitle : "$basePageTitle &ndash;&nbsp;$pageSubtitle";
        include self::VIEWS_DIRECTORY.View::head->value.".php";
        self::printNewLine();
    }

    public static function buildTopBar(User $_USER): void {
        include self::VIEWS_DIRECTORY.View::topBar->value.".php";
        self::printNewLine();
    }

    public static function buildMenu(User $_USER): void {
        include self::VIEWS_DIRECTORY.View::menu->value.".php";
        self::printNewLine();
    }

    private static function getBasePageTitle(): string {
        if (is_null(self::$basePageTitle)) {
            $properties = PropertiesReader::getProperties("application");
            self::$basePageTitle = $properties["basePageTitle"];
        }

        return self::$basePageTitle;
    }

    private static function printNewLine(): void {
        echo PHP_EOL; // For clean HTML formatting.
    }
}

?>