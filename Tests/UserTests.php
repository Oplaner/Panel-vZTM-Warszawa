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
        } elseif ($user->isActive()) {
            return "New user should be inactive.";
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

    public static function getExistingUserByLogin(): bool|string {
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);
        $user = User::withLogin($user->getLogin());

        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
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
        self::updateUsername($user->getLogin(), $username1.rand());
        $user->updateUsername();
        $username2 = $user->getUsername();
        self::updateUsername($user->getLogin(), $username1);

        TestHelpers::deleteTestUser($user->getID());

        if ($username1 == $username2) {
            return "Expected the username versions to be different after update. Before: \"$username1\", after: \"$username2\".";
        }

        return true;
    }

    public static function checkUserBecomesActiveWhenTheyGetActiveProfile(): bool|string {
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);
        $valueBeforeChange = $user->isActive();
        $profile = TestHelpers::createTestDirectorProfile($user);
        $valueAfterChange = $user->isActive();

        TestHelpers::deleteTestDirectorProfileData($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueBeforeChange == true) {
            return "User should be inactive before getting active profile.";
        } elseif ($valueAfterChange == false) {
            return "User should become active after getting active profile.";
        }

        return true;
    }

    public static function checkUserRemainsActiveWhenTheyLoseOneOfTwoActiveProfiles(): bool|string {
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);
        $profile1 = TestHelpers::createTestPersonnelProfile($user);
        $profile2 = TestHelpers::createTestDirectorProfile($user);
        $valueBeforeChange = $user->isActive();
        $profile1->deactivate($user);
        $valueAfterChange = $user->isActive();

        TestHelpers::deleteTestPrivilege($profile1->getPrivileges()[0]->getID());
        TestHelpers::deleteTestPersonnelProfileData($profile1->getID());
        TestHelpers::deleteTestDirectorProfileData($profile2->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueBeforeChange == false) {
            return "User should be active before losing one active profile.";
        } elseif ($valueAfterChange == false) {
            return "User should remain active after losing only one of two active profiles.";
        }

        return true;
    }

    public static function checkUserBecomesInactiveWhenTheyLoseLastActiveProfile(): bool|string {
        $user = User::createNew(TestHelpers::EXISTING_TEST_USER_LOGIN);
        $profile = TestHelpers::createTestDirectorProfile($user);
        $valueBeforeChange = $user->isActive();
        $profile->deactivate($user);
        $valueAfterChange = $user->isActive();

        TestHelpers::deleteTestDirectorProfileData($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueBeforeChange == false) {
            return "User should be active before losing last active profile.";
        } elseif ($valueAfterChange == true) {
            return "User should become inactive after losing last active profile.";
        }

        return true;
    }

    private static function updateUsername(int $myBBUserID, string $username): void {
        DatabaseConnector::shared()->execute_query(
            "UPDATE mybb18_users
            SET username = ?
            WHERE uid = ?",
            [
                $username,
                $myBBUserID
            ]
        );
    }
}

?>