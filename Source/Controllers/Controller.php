<?php

abstract class Controller {
    private const VIEWS_DIRECTORY = __DIR__."/../Views/";

    protected static function makeFirstPageInputArray(): array {
        return [
            Router::PATH_DATA_KEY => [
                "pageNumber" => 1
            ]
        ];
    }

    protected static function getNumberOfObjectsPerPage(): int {
        $properties = PropertiesReader::getProperties("application");
        return $properties["numberOfObjectsPerPage"];
    }

    protected static function makeErrorMessage(array $errors): string {
        return join("<br>", $errors);
    }

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