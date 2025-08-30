<?php

abstract class Controller {
    private const VIEWS_DIRECTORY = __DIR__."/../Views/";

    protected static function renderView(View $view, array $parameters = []): void {
        global $_USER;
        extract($parameters);
        include self::VIEWS_DIRECTORY.$view->value.".php";
    }

    protected static function renderJSON(array $jsonObject): void {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($jsonObject, JSON_UNESCAPED_UNICODE);
    }
}

?>