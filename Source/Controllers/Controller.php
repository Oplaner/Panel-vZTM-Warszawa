<?php

abstract class Controller {
    private const VIEWS_DIRECTORY = __DIR__."/../Views/";

    public static function renderView(string $name, array $parameters = []): void {
        global $_USER;
        extract($parameters);
        include self::VIEWS_DIRECTORY.$name.".php";
    }
}

?>