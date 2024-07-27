<?php

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

        if (isset($user)) {
            return "Expected null value. Found: ".gettype($user).".";
        }

        return true;
    }

    public static function getExistingUser(): bool|string {
        self::deleteTestUser();
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $user->save();
        $userID = $user->getID();
        unset($user);
        $user = User::withID($userID);
        self::deleteTestUser();

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
        } elseif ($user->getLogin() != self::EXISTING_TEST_USER_LOGIN) {
            return "User's login is incorrect. Expected: \"".self::EXISTING_TEST_USER_LOGIN."\", found: \"{$user->getLogin()}\".";
        } elseif ($user->getUsername() != self::EXISTING_TEST_USER_USERNAME) {
            return "User's username is incorrect. Expected: \"".self::EXISTING_TEST_USER_USERNAME."\", found: \"{$user->getUsername()}\".";
        }

        return true;
    }

    public static function getNotExistingUser(): bool|string {
        $user = User::withID(User::generateUUIDv4());

        if (!is_null($user)) {
            return "Expected null value. Found: ".gettype($user).".";
        }

        return true;
    }

    public static function checkUsernameIsUpdated(): bool|string {
        self::deleteTestUser();
        $db = DatabaseConnector::shared();
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $user->save();
        $userID = $user->getID();
        unset($user);
        DatabaseEntity::removeFromCache($userID);
        $user = User::withID($userID);
        $username1 = $user->getUsername();

        $db->execute_query(
            "UPDATE mybb18_users
            SET username = ?
            WHERE uid = ?",
            [
                $username1.rand(),
                $user->getLogin()
            ]
        );

        $user->updateUsername();
        $username2 = $user->getUsername();

        $db->execute_query(
            "UPDATE mybb18_users
            SET username = ?
            WHERE uid = ?",
            [
                $username1,
                $user->getLogin()
            ]
        );

        self::deleteTestUser();

        if ($username1 == $username2) {
            return "Expected the username versions to be different after update. Before: \"$username1\", after: \"$username2\".";
        }

        return true;
    }

    private static function deleteTestUser() {
        DatabaseConnector::shared()->execute_query(
            "DELETE FROM users
            WHERE login = ?",
            [
                self::EXISTING_TEST_USER_LOGIN
            ]
        );
    }
}

?>