<?php

require_once __DIR__."/../Enums/RequestMethod.php";

define("DEFAULT_ROUTE", true);

#[Attribute(Attribute::TARGET_METHOD)]
final class Route {
    public string $path;
    public RequestMethod $method;
    public bool $isDefault;

    public function __construct(string $path, RequestMethod $method, bool $isDefault = false) {
        $this->path = $path;
        $this->method = $method;
        $this->isDefault = $isDefault;
    }
}

?>