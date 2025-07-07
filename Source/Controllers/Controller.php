<?php

abstract class Controller {
    private const VIEWS_DIRECTORY = __DIR__."/../Views/";

    protected static function renderView(View $view, array $parameters = []): void {
        global $_USER;
        extract($parameters);
        include self::VIEWS_DIRECTORY.$view->value.".php";
    }
}

?>