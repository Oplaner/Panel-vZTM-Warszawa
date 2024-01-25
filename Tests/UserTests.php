<?php

require_once "../Source/Models/Classes/DatabaseConnector.php";
require_once "../Source/Models/Classes/User.php";

final class UserTests {
    private const TEST_USER_LOGIN = 1387;

    public static function createNewUser(): bool|string {
        self::deleteTestUser();
        $user = User::createNew(self::TEST_USER_LOGIN);

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
        } else {
            return true;
        }
    }

    public static function checkNewUserIDPattern(): bool|string {
        self::deleteTestUser();
        $user = User::createNew(self::TEST_USER_LOGIN);
        $id = $user->getID();
        $pattern = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/";

        if (!preg_match($pattern, $id)) {
            return "New user's ID \"$id\" does not match correct pattern: $pattern.";
        } else {
            return true;
        }
    }

    private static function deleteTestUser() {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM users
            WHERE login = ?",
            [
                self::TEST_USER_LOGIN
            ]
        );
    }
}

?>