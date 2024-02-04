<?php

require_once "../Source/Models/Classes/DatabaseConnector.php";
require_once "../Source/Models/Classes/User.php";

final class UserTests {
    private const EXISTING_TEST_USER_LOGIN = 1387;
    private const EXISTING_TEST_USER_USERNAME = "Oplaner";
    private const NOT_EXISTING_TEST_USER_LOGIN = 100;

    public static function createNewExistingUser(): bool|string {
        self::deleteTestUser();
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
        } elseif ($user->getLogin() != self::EXISTING_TEST_USER_LOGIN) {
            return "User's login is incorrect. Expected: \"".self::EXISTING_TEST_USER_LOGIN."\", found: \"{$user->getLogin()}\".";
        } elseif ($user->getUsername() != self::EXISTING_TEST_USER_USERNAME) {
            return "User's username is incorrect. Expected: \"".self::EXISTING_TEST_USER_USERNAME."\", found: \"{$user->getUsername()}\".";
        } elseif (!$user->shouldChangePassword()) {
            return "New user is expected to change their password.";
        }

        return true;
    }

    public static function createNewNotExistingUser(): bool|string {
        $user = User::createNew(self::NOT_EXISTING_TEST_USER_LOGIN);

        if (!is_null($user)) {
            return "Expected null value. Found: ".gettype($user).".";
        }

        return true;
    }

    private static function deleteTestUser() {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM users
            WHERE login = ?",
            [
                self::EXISTING_TEST_USER_LOGIN
            ]
        );
    }
}

?>