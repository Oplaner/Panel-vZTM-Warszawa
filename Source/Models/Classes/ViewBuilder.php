<?php

final class ViewBuilder {
    private const VIEWS_DIRECTORY = __DIR__."/../../Views/";
    private const HEAD_VIEW_NAME = "Head";
    private const TOP_BAR_VIEW_NAME = "TopBar";
    private const MENU_VIEW_NAME = "Menu";

    private static ?string $basePageTitle = null;

    public static function buildHead(Style $style, array $scripts, ?string $pageSubtitle): void {
        $basePageTitle = self::getBasePageTitle();
        $pageTitle = is_null($pageSubtitle) ? $basePageTitle : "$basePageTitle &ndash;&nbsp;$pageSubtitle";
        include self::VIEWS_DIRECTORY.self::HEAD_VIEW_NAME.".php";
        self::printNewLine();
    }

    public static function buildTopBar(User $_USER): void {
        include self::VIEWS_DIRECTORY.self::TOP_BAR_VIEW_NAME.".php";
        self::printNewLine();
    }

    public static function buildMenu(array $userProfiles): void {
        include self::VIEWS_DIRECTORY.self::MENU_VIEW_NAME.".php";
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
        echo "\n"; // For clean HTML formatting.
    }
}

?>