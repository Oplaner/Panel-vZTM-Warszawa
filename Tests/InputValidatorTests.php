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
            ["", true],
            ["a", false],
            ["0", false],
            ["1", false]
        ];

        for ($i = 0; $i < count($testData); $i++) {
            $value = $testData[$i][0];
            $shouldThrowException = $testData[$i][1];
            $didThrowException = false;

            try {
                InputValidator::checkNonEmpty($value);
            } catch (ValidationException) {
                $didThrowException = true;
            }

            if ($didThrowException != $shouldThrowException) {
                return "Non-empty value validation failed for example at index $i.";
            }
        }

        return true;
    }

    public static function checkValidationForLength(): bool|string {
        $testData = [
            [["", 0, 1], false],
            [["", 1, 2], true],
            [["abc", 4, 10], true],
            [["abc", 3, 10], false],
            [["abc", 2, 3], false],
            [["żółć", 3, 4], false],
            [["żółć", 5, 10], true],
            [["żółć", 1, 2], true]
        ];

        for ($i = 0; $i < count($testData); $i++) {
            $parameters = $testData[$i][0];
            $shouldThrowException = $testData[$i][1];
            $didThrowException = false;

            try {
                InputValidator::checkLength($parameters[0], $parameters[1], $parameters[2]);
            } catch (ValidationException) {
                $didThrowException = true;
            }

            if ($didThrowException != $shouldThrowException) {
                return "Value length validation failed for example at index $i.";
            }

            $i++;
        }

        return true;
    }

    public static function checkValidationForInteger(): bool|string {
        $testData = [
            [["", 0, 1], true],
            [["a", 0, 1], true],
            [["0a", 0, 1], true],
            [["0", 0, 1], false],
            [["-1", -1, 1], false],
            [["-1", 0, 1], true],
            [["1", -1, 0], true],
            [["1", 0, 1], false],
            [["3.14", 3, 4], true],
            [["3,14", 3, 4], true]
        ];

        for ($i = 0; $i < count($testData); $i++) {
            $parameters = $testData[$i][0];
            $shouldThrowException = $testData[$i][1];
            $didThrowException = false;

            try {
                InputValidator::checkInteger($parameters[0], $parameters[1], $parameters[2]);
            } catch (ValidationException) {
                $didThrowException = true;
            }

            if ($didThrowException != $shouldThrowException) {
                return "Integer validation failed for example at index $i.";
            }

            $i++;
        }

        return true;
    }
}

?>