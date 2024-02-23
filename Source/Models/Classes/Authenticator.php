<?php

require_once __DIR__."/PropertiesReader.php";

final class Authenticator {
    private const TEMPORARY_PASSWORD_CHARACTER_GROUPS = [
        "uppercaseLetters" => "ABCDEFGHJKLMNPQRTUVWXY",
        "lowercaseLetters" => "abcdefghjkmnpqrstuvwxyz",
        "digits" => "346789",
        "specialCharacters" => "!@#$%&?"
    ];

    public static function generateTemporaryPassword(): string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minPasswordLength = $properties["minPasswordLength"];
        $minNumberOfUppercaseLetters = $properties["minNumberOfUppercaseLetters"];
        $minNumberOfLowercaseLetters = $properties["minNumberOfLowercaseLetters"];
        $minNumberOfDigits = $properties["minNumberOfDigits"];
        $minNumberOfSpecialCharacters = $properties["minNumberOfSpecialCharacters"];
        $uppercaseLetters = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["uppercaseLetters"]);
        $lowercaseLetters = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["lowercaseLetters"]);
        $digits = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["digits"]);
        $specialCharacters = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["specialCharacters"]);
        $allCharacters = array_merge($uppercaseLetters, $lowercaseLetters, $digits, $specialCharacters);
        $passwordCharacterPool = [];

        for ($i = 0; $i < $minNumberOfUppercaseLetters; $i++) {
            $passwordCharacterPool[] = $uppercaseLetters[rand(0, count($uppercaseLetters) - 1)];
        }

        for ($i = 0; $i < $minNumberOfLowercaseLetters; $i++) {
            $passwordCharacterPool[] = $lowercaseLetters[rand(0, count($lowercaseLetters) - 1)];
        }

        for ($i = 0; $i < $minNumberOfDigits; $i++) {
            $passwordCharacterPool[] = $digits[rand(0, count($digits) - 1)];
        }

        for ($i = 0; $i < $minNumberOfSpecialCharacters; $i++) {
            $passwordCharacterPool[] = $specialCharacters[rand(0, count($specialCharacters) - 1)];
        }

        while (count($passwordCharacterPool) < $minPasswordLength) {
            $passwordCharacterPool[] = $allCharacters[rand(0, count($allCharacters) - 1)];
        }

        shuffle($passwordCharacterPool);
        return implode($passwordCharacterPool);
    }

    public static function passwordFulfillsRequirements($password): bool {
        $properties = PropertiesReader::getProperties("authenticator");
        $minPasswordLength = $properties["minPasswordLength"];
        $minNumberOfUppercaseLetters = $properties["minNumberOfUppercaseLetters"];
        $minNumberOfLowercaseLetters = $properties["minNumberOfLowercaseLetters"];
        $minNumberOfDigits = $properties["minNumberOfDigits"];
        $minNumberOfSpecialCharacters = $properties["minNumberOfSpecialCharacters"];

        if (mb_strlen($password, "UTF-8") < $minPasswordLength
        || preg_match_all("/\p{Lu}/u", $password) < $minNumberOfUppercaseLetters
        || preg_match_all("/\p{Ll}/u", $password) < $minNumberOfLowercaseLetters
        || preg_match_all("/\d/", $password) < $minNumberOfDigits
        || preg_match_all("/[^\p{Lu}\p{Ll}\d\s]/u", $password) < $minNumberOfSpecialCharacters) {
            return false;
        }

        return true;
    }
}

?>