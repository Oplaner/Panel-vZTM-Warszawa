<?php

final class InputValidator {
    private const ENCODING = "UTF-8";

    public static function clean(string $value): string {
        return mb_trim($value, null, self::ENCODING);
    }

    public static function nonEmpty(string $value): bool {
        return !is_null($value) && $value != "";
    }

    public static function length(string $value, int $min, int $max): bool {
        $length = mb_strlen($value, self::ENCODING);
        return $length >= $min && $length <= $max;
    }
}

?>