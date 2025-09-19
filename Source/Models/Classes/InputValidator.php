<?php

final class InputValidator {
    private const ENCODING = "UTF-8";

    public static function clean(?string $value): ?string {
        if (is_null($value)) {
            return $value;
        }

        return mb_trim($value, null, self::ENCODING);
    }

    public static function nonEmpty(string $value): bool {
        return $value != "";
    }

    public static function length(string $value, int $min, int $max): bool {
        $length = mb_strlen($value, self::ENCODING);
        return $length >= $min && $length <= $max;
    }
}

?>