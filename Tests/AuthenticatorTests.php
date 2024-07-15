<?php

final class AuthenticatorTests {
    public static function checkTooShortPasswordIsIncorrect(): bool|string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minPasswordLength = $properties["minPasswordLength"];
        $password = "";

        for ($i = 0; $i < $minPasswordLength - 1; $i++) {
            $password .= "a";
        }

        if (Authenticator::passwordFulfillsRequirements($password)) {
            return "Generated password \"$password\" of length ".strlen($password)." should not be correct. The minimum length is $minPasswordLength characters.";
        }

        return true;
    }

    public static function checkPasswordWithoutUppercaseLettersIsIncorrect(): bool|string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minNumberOfUppercaseLetters = $properties["minNumberOfUppercaseLetters"];
        $password = preg_replace("/\p{Lu}/u", "a", Authenticator::generateTemporaryPassword());

        if (Authenticator::passwordFulfillsRequirements($password)) {
            return "Generated password \"$password\" without uppercase letters should not be correct. The minimum number of such characters is $minNumberOfUppercaseLetters.";
        }

        return true;
    }

    public static function checkPasswordWithoutLowercaseLettersIsIncorrect(): bool|string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minNumberOfLowercaseLetters = $properties["minNumberOfLowercaseLetters"];
        $password = preg_replace("/\p{Ll}/u", "A", Authenticator::generateTemporaryPassword());

        if (Authenticator::passwordFulfillsRequirements($password)) {
            return "Generated password \"$password\" without lowercase letters should not be correct. The minimum number of such characters is $minNumberOfLowercaseLetters.";
        }

        return true;
    }

    public static function checkPasswordWithoutDigitsIsIncorrect(): bool|string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minNumberOfDigits = $properties["minNumberOfDigits"];
        $password = preg_replace("/\d/", "a", Authenticator::generateTemporaryPassword());

        if (Authenticator::passwordFulfillsRequirements($password)) {
            return "Generated password \"$password\" without digits should not be correct. The minimum number of such characters is $minNumberOfDigits.";
        }

        return true;
    }

    public static function checkPasswordWithoutSpecialCharactersIsIncorrect(): bool|string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minNumberOfSpecialCharacters = $properties["minNumberOfSpecialCharacters"];
        $password = preg_replace("/[^\p{Lu}\p{Ll}\d\s]/u", "a", Authenticator::generateTemporaryPassword());

        if (Authenticator::passwordFulfillsRequirements($password)) {
            return "Generated password \"$password\" without special characters should not be correct. The minimum number of such characters is $minNumberOfSpecialCharacters.";
        }

        return true;
    }

    public static function check100GeneratedTemporaryPasswordsFulfillRequirements(): bool|string {
        for ($i = 0; $i < 100; $i++) {
            $password = Authenticator::generateTemporaryPassword();
            
            if (!Authenticator::passwordFulfillsRequirements($password)) {
                return "Generated password \"$password\" does not fulfill password requirements.";
            }
        }

        return true;
    }
}

?>