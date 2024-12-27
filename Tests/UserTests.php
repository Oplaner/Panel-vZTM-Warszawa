<?php

final class UserTests {
    public static function createNewExistingUser(): bool|string {
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);

        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
        } elseif ($user->getLogin() != TestHelpers::EXISTING_TEST_USER_LOGIN) {
            return "User's login is incorrect. Expected: \"".TestHelpers::EXISTING_TEST_USER_LOGIN."\", found: \"{$user->getLogin()}\".";
        } elseif ($user->getUsername() != TestHelpers::EXISTING_TEST_USER_USERNAME) {
            return "User's username is incorrect. Expected: \"".TestHelpers::EXISTING_TEST_USER_USERNAME."\", found: \"{$user->getUsername()}\".";
        } elseif (!$user->shouldChangePassword()) {
            return "New user is expected to change their password.";
        }

        return true;
    }

    public static function createNewNotExistingUser(): bool|string {
        $user = User::createNew(TestHelpers::NOT_EXISTING_TEST_USER_LOGIN);

        if (isset($user)) {
            return "Expected null value. Found: ".gettype($user).".";
        }

        return true;
    }

    public static function getExistingUser(): bool|string {
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);
        DatabaseEntity::removeFromCache($user);
        $user = User::withID($user->getID());

        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
        } elseif ($user->getLogin() != TestHelpers::EXISTING_TEST_USER_LOGIN) {
            return "User's login is incorrect. Expected: \"".TestHelpers::EXISTING_TEST_USER_LOGIN."\", found: \"{$user->getLogin()}\".";
        } elseif ($user->getUsername() != TestHelpers::EXISTING_TEST_USER_USERNAME) {
            return "User's username is incorrect. Expected: \"".TestHelpers::EXISTING_TEST_USER_USERNAME."\", found: \"{$user->getUsername()}\".";
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
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);
        $username1 = $user->getUsername();

        DatabaseConnector::shared()->execute_query(
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

        TestHelpers::deleteTestUser($user->getID());

        if ($username1 == $username2) {
            return "Expected the username versions to be different after update. Before: \"$username1\", after: \"$username2\".";
        }

        return true;
    }
}

?>