<?php

final class Router {
    public const PATH_DATA_KEY = "pathData";
    public const POST_DATA_KEY = "postData";

    private const CONTROLLERS_DIRECTORY = __DIR__."/../../Controllers/";
    private const CONTROLLER_KEY = "controller";
    private const ACTION_KEY = "action";
    private const ACCESS_KEY = "access";

    private array $routes;

    public function __construct() {
        $this->registerRoutes();
    }

    public static function redirect(string $path): void {
        header("Location: ".PathBuilder::action($path));
    }

    public static function redirectToHome(): void {
        header("Location: ".PathBuilder::root());
    }

    public function dispatchRequest(string $path, RequestMethod $method): void {
        Logger::log(LogLevel::info, "Handling ".strtoupper($method->name)." request for path: \"$path\".");
        $rootPath = PathBuilder::root();
        $base = $rootPath == "/" ? "" : preg_quote($rootPath, "/");

        foreach (array_keys($this->routes) as $routePattern) {
            $matches = [];

            if (preg_match("/^{$base}{$routePattern}$/", $path, $matches)
            && array_key_exists($method->name, $this->routes[$routePattern])) {
                $controller = $this->routes[$routePattern][$method->name][self::CONTROLLER_KEY];
                $action = $this->routes[$routePattern][$method->name][self::ACTION_KEY];
                $access = $this->routes[$routePattern][$method->name][self::ACCESS_KEY];
                $input = [
                    self::PATH_DATA_KEY => array_filter(
                        $matches,
                        fn($key) => is_string($key),
                        ARRAY_FILTER_USE_KEY
                    ),
                    self::POST_DATA_KEY => $_POST
                ];

                if (!AccessChecker::userCanAccess($access, $input[self::PATH_DATA_KEY])) {
                    Logger::log(LogLevel::info, "Access denied. Redirecting to home.");
                    self::redirectToHome();
                }

                $controller = new $controller();
                $controller->$action($input);
                return;
            }
        }

        Logger::log(LogLevel::info, "Invalid route. Redirecting to home.");
        self::redirectToHome();
    }

    private function registerRoutes(): void {
        $controllerNames = array_map(
            fn($file) => preg_replace("/^(\S+)\.php$/", "$1", $file),
            array_filter(
                array_diff(
                    scandir(self::CONTROLLERS_DIRECTORY),
                    [".", ".."]
                ),
                fn($file) => preg_match("/^\S+Controller\.php$/", $file)
            )
        );

        foreach ($controllerNames as $controller) {
            $reflection = new ReflectionClass($controller);

            foreach ($reflection->getMethods() as $method) {
                $routeAttributes = $method->getAttributes(Route::class);
                $accessAttributes = $method->getAttributes(Access::class);
                $action = $method->getShortName();

                foreach ($routeAttributes as $routeAttribute) {
                    $route = $routeAttribute->newInstance();
                    $path = preg_replace("/\\\{(.+?)\\\}/", "(?<$1>[[:alnum:]-]+)", preg_quote($route->path, "/"));
                    $access = null;

                    if (count($accessAttributes) == 1) {
                        $access = $accessAttributes[0]->newInstance();
                    } else {
                        $access = new Access(AccessGroup::everyone);
                    }

                    $this->routes[$path][$route->method->name][self::CONTROLLER_KEY] = $controller;
                    $this->routes[$path][$route->method->name][self::ACTION_KEY] = $action;
                    $this->routes[$path][$route->method->name][self::ACCESS_KEY] = $access;
                }
            }
        }
    }
}

?>