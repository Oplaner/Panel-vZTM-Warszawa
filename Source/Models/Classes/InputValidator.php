<?php

final class InputValidator {
    public const MESSAGE_TEMPLATE_NON_EMPTY = "Pole \"{}\" nie powinno być puste.";
    public const MESSAGE_TEMPLATE_LENGTH = "Wartość w polu \"{}\" powinna mieć długość od {} do {} znaków.";
    public const MESSAGE_TEMPLATE_INTEGER = "Wartość w polu \"{}\" powinna być liczbą całkowitą od {} do {}.";
    public const MESSAGE_TEMPLATE_GENERIC = "Wartość w polu \"{}\" jest niepoprawna.";

    private const ENCODING = "UTF-8";
    private const MESSAGE_PARAMETER_PLACEHOLDER = "{}";

    public static function clean(?string $value): ?string {
        if (is_null($value)) {
            return $value;
        }

        return mb_trim($value, null, self::ENCODING);
    }

    public static function checkNonEmpty(string $value, ?string $fieldName = null): void {
        if ($value == "") {
            $message = is_null($fieldName) ? "" : self::generateErrorMessage(self::MESSAGE_TEMPLATE_NON_EMPTY, $fieldName);
            throw new ValidationException($message);
        }
    }

    public static function checkLength(string $value, int $min, int $max, ?string $fieldName = null): void {
        $length = mb_strlen($value, self::ENCODING);

        if ($length < $min || $length > $max) {
            $message = is_null($fieldName) ? "" : self::generateErrorMessage(self::MESSAGE_TEMPLATE_LENGTH, $fieldName, $min, $max);
            throw new ValidationException($message);
        }
    }

    public static function checkInteger(string $value, int $min, int $max, ?string $fieldName = null): void {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || $value < $min || $value > $max) {
            $message = is_null($fieldName) ? "" : self::generateErrorMessage(self::MESSAGE_TEMPLATE_INTEGER, $fieldName, $min, $max);
            throw new ValidationException($message);
        }
    }

    public static function generateErrorMessage(string $template, string ...$parameters): string {
        return vsprintf(str_replace(self::MESSAGE_PARAMETER_PLACEHOLDER, "%s", $template), $parameters);
    }
}

?>