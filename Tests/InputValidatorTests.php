<?php

final class InputValidatorTests {
    public static function checkCleaning(): bool|string {
        $testData = [
            [null, null],
            ["", ""],
            [" ", ""],
            ["   ", ""],
            ["
            ", ""],
            ["a ", "a"],
            [" a", "a"],
            [" a ", "a"]
        ];

        for ($i = 0; $i < count($testData); $i++) {
            $value = $testData[$i][0];
            $result = $testData[$i][1];

            if (InputValidator::clean($value) != $result) {
                return "Cleaning failed for example at index $i.";
            }
        }

        return true;
    }

    public static function checkValidationForNonEmptyValue(): bool|string {
        $testData = [
            ["", false],
            ["a", true],
            ["0", true],
            ["1", true]
        ];

        for ($i = 0; $i < count($testData); $i++) {
            $value = $testData[$i][0];
            $result = $testData[$i][1];

            if (InputValidator::nonEmpty($value) != $result) {
                return "Non-empty value validation failed for example at index $i.";
            }
        }

        return true;
    }

    public static function checkValidationForLength(): bool|string {
        $testData = [
            [["", 0, 1], true],
            [["", 1, 2], false],
            [["abc", 4, 10], false],
            [["abc", 3, 10], true],
            [["abc", 2, 3], true],
            [["żółć", 3, 4], true],
            [["żółć", 5, 10], false],
            [["żółć", 1, 2], false]
        ];

        for ($i = 0; $i < count($testData); $i++) {
            $parameters = $testData[$i][0];
            $result = $testData[$i][1];

            if (InputValidator::length($parameters[0], $parameters[1], $parameters[2]) != $result) {
                return "Value length validation failed for example at index $i.";
            }

            $i++;
        }

        return true;
    }
}

?>