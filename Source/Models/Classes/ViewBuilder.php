<?php

final class ViewBuilder {
    private const VIEWS_DIRECTORY = __DIR__."/../../Views/";
    private const INDENT_STRING = "    "; // 4 spaces.

    private static ?string $basePageTitle = null;

    public static function buildHead(Style $style, array $scripts, ?string $pageSubtitle): void {
        $basePageTitle = self::getBasePageTitle();
        $pageTitle = is_null($pageSubtitle) ? $basePageTitle : "$basePageTitle &ndash;&nbsp;$pageSubtitle";
        self::buildView(View::head, 0, compact("style", "scripts", "pageTitle"));
    }

    public static function buildTopBar(User $_USER): void {
        self::buildView(View::topBar, 1, compact("_USER"));
    }

    public static function buildMenu(User $_USER): void {
        self::buildView(View::menu, 1, compact("_USER"));
    }

    public static function buildPagination(PaginationInfo $paginationInfo, string $basePath): void {
        self::buildView(View::pagination, 2, compact("paginationInfo", "basePath"));
    }

    public static function buildView(View $view, int $indentLevel, array $parameters = []): void {
        extract($parameters);
        ob_start();
        include self::VIEWS_DIRECTORY.$view->value.".php";
        $content = ob_get_clean();
        $indent = str_repeat(self::INDENT_STRING, $indentLevel);
        echo preg_replace("/^/m", $indent, $content).PHP_EOL;
    }

    private static function getBasePageTitle(): string {
        if (is_null(self::$basePageTitle)) {
            $properties = PropertiesReader::getProperties("application");
            self::$basePageTitle = $properties["basePageTitle"];
        }

        return self::$basePageTitle;
    }
}

?>