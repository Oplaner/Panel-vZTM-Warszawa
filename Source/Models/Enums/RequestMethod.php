<?php

enum RequestMethod {
    case get;
    case post;

    public static function fromString(string $serverRequestMethod): RequestMethod {
        $serverRequestMethod = strtolower($serverRequestMethod);

        foreach (self::cases() as $method) {
            if ($method->name == $serverRequestMethod) {
                return $method;
            }
        }

        return RequestMethod::get;
    }
}

?>