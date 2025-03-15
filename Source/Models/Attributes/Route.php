<?php

define("DEFAULT_ROUTE", true);

#[Attribute(Attribute::TARGET_METHOD)]
final class Route {
    public readonly string $path;
    public readonly RequestMethod $method;
    public readonly bool $isDefault;

    public function __construct(string $path, RequestMethod $method, bool $isDefault = false) {
        $this->path = $path;
        $this->method = $method;
        $this->isDefault = $isDefault;
    }
}

?>