<?php

require_once __DIR__."/Route.php";
require_once __DIR__."/../Enums/RequestMethod.php";

final class Router {
    private const CONTROLLERS_DIRECTORY = __DIR__."/../../Controllers/";
    private string $base;
    private array $routes;
    private string $defaultController;
    private string $defaultAction;

    public function __construct(string $base) {
        $this->base = $base;
        $this->registerRoutes();
    }

    public function dispatchRequest(string $path, RequestMethod $method): void {
        Logger::log(LogLevel::info, "Handling ".strtoupper($method->name)." request for path: \"$path\".");

        foreach (array_keys($this->routes) as $routePattern) {
            $matches = [];

            if (preg_match("/{$this->base}$routePattern$/", $path, $matches)
            && array_key_exists($method->name, $this->routes[$routePattern])) {
                $controller = $this->routes[$routePattern][$method->name]["controller"];
                $action = $this->routes[$routePattern][$method->name]["action"];
                $input = [
                    "pathData" => array_filter(
                        $matches,
                        fn ($key) => is_string($key),
                        ARRAY_FILTER_USE_KEY
                    ),
                    "postData" => $_POST
                ];

                $controller = new $controller();
                $controller->$action($input);
                return;
            }
        }

        $controller = new $this->defaultController();
        $controller->{$this->defaultAction}();
    }

    private function registerRoutes(): void {
        $controllerNames = array_map(
            fn ($file) => preg_replace("/^(\S+)\.php$/", "$1", $file),
            array_filter(
                array_diff(
                    scandir(self::CONTROLLERS_DIRECTORY),
                    [".", ".."]
                ),
                fn ($file) => preg_match("/^\S+Controller\.php$/", $file)
            )
        );

        foreach ($controllerNames as $controller) {
            require_once self::CONTROLLERS_DIRECTORY.$controller.".php";
            $reflection = new ReflectionClass($controller);

            foreach ($reflection->getMethods() as $method) {
                foreach ($method->getAttributes(Route::class) as $attribute) {
                    $route = $attribute->newInstance();
                    $path = preg_replace("/\\\{(.+?)\\\}/", "(?<$1>.+)", preg_quote($route->path, "/"));
                    $action = $method->getShortName();
                    $this->routes[$path][$route->method->name]["controller"] = $controller;
                    $this->routes[$path][$route->method->name]["action"] = $action;

                    if ($route->isDefault) {
                        $this->defaultController = $controller;
                        $this->defaultAction = $action;
                    }
                }
            }
        }
    }
}

?>