<?php

final class UserTests {
    private const EXISTING_TEST_USER_LOGIN = 1387;
    private const EXISTING_TEST_USER_USERNAME = "Oplaner";
    private const NOT_EXISTING_TEST_USER_LOGIN = 100;

    public static function createNewExistingUser(): bool|string {
        self::deleteTestUser();
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        self::deleteTestUser();

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
        $userID = $user->getID();
        DatabaseEntity::removeFromCache($user);

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

    public static function getUserProfiles(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);

        $directorProfile = DirectorProfile::createNew($user, $user);
        $directorProfileID = $directorProfile->getID();
        DatabaseEntity::removeFromCache($directorProfile);

        $description = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew(PrivilegeScope::canViewAllTimetables);
        $privilegeID = $privilege->getID();
        $personnelProfile = PersonnelProfile::createNew($user, $user, $description, [$privilege]);
        $personnelProfileID = $personnelProfile->getID();
        DatabaseEntity::removeFromCache($privilege);
        DatabaseEntity::removeFromCache($personnelProfile);

        $profiles = $user->getProfiles();

        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM profiles_director
            WHERE profile_id = ?",
            [
                $directorProfileID
            ]
        );
        $db->execute_query(
            "DELETE FROM profiles
            WHERE id = ?",
            [
                $directorProfileID
            ]
        );
        $db->execute_query(
            "DELETE FROM privileges
            WHERE id = ?",
            [
                $privilegeID
            ]
        );
        $db->execute_query(
            "DELETE FROM personnel_profile_privileges
            WHERE personnel_profile_id = ?",
            [
                $personnelProfileID
            ]
        );
        $db->execute_query(
            "DELETE FROM profiles_personnel
            WHERE profile_id = ?",
            [
                $personnelProfileID
            ]
        );
        $db->execute_query(
            "DELETE FROM profiles
            WHERE id = ?",
            [
                $personnelProfileID
            ]
        );
        self::deleteTestUser();

        if (count($profiles) != 2) {
            return "The number of user profiles is incorrect. Expected: 2, found: ".count($profiles).".";
        } elseif (!is_a($profiles[0], DirectorProfile::class)) {
            return "The first user profile is of incorrect type. Expected: ".DirectorProfile::class.", found: ".gettype($profiles[0]).".";
        } elseif (!is_a($profiles[1], PersonnelProfile::class)) {
            return "The second user profile is of incorrect type. Expected: ".PersonnelProfile::class.", found: ".gettype($profiles[1]).".";
        } elseif (count($profiles[1]->getPrivileges()) != 1) {
            return "User's personnel profile has incorrect number of privileges. Expected: 1, found: ".count($profiles[1]->getPrivileges()).".";
        } elseif ($profiles[1]->getPrivileges()[0]->getScope() != PrivilegeScope::canViewAllTimetables) {
            return "User's personnel profile privilege scope is incorrect. Expected: ".PrivilegeScope::canViewAllTimetables->name.", found: ".$profiles[1]->getPrivileges()[0]->getScope()->name.".";
        }

        return true;
    }

    public static function getUserContracts(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);

        $contract = Contract::createNew($user, $user, ContractState::regular, 0);
        $contractIDs = [$contract->getID()];
        $contractStates = [$contract->getCurrentState()];
        DatabaseEntity::removeFromCache($contract);

        $contract = Contract::createNew($user, $user, ContractState::conditional, 0);
        $contractIDs[] = $contract->getID();
        $contractStates[] = $contract->getCurrentState();
        DatabaseEntity::removeFromCache($contract);

        $contracts = $user->getContracts();

        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM contract_periods
            WHERE contract_id = ? OR contract_id = ?",
            $contractIDs
        );
        $db->execute_query(
            "DELETE FROM contracts
            WHERE id = ? OR id = ?",
            $contractIDs
        );
        self::deleteTestUser();

        if (count($contracts) != 2) {
            return "The number of user contracts is incorrect. Expected: 2, found: ".count($contracts).".";
        } elseif ($contracts[0]->getCurrentState() != $contractStates[0]) {
            return "The first user contract state is incorrect. Expected: {$contractStates[0]->name}, found: {$contracts[0]->getCurrentState()->name}.";
        } elseif ($contracts[1]->getCurrentState() != $contractStates[1]) {
            return "The second user contract state is incorrect. Expected: {$contractStates[1]->name}, found: {$contracts[1]->getCurrentState()->name}.";
        }

        return true;
    }

    private static function deleteTestUser(): void {
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