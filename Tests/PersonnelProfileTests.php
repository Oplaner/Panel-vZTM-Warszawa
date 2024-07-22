<?php

final class PersonnelProfileTests {
    private const EXISTING_TEST_USER_LOGIN = 1387;

    public static function throwExceptionWhenCreatingPersonnelProfileWithoutPrivileges(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);

        try {
            PersonnelProfile::createNew($user, $user, "", []);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when creating a personnel profile without privileges.";
    }

    public static function createNewPersonnelProfile(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $description = DatabaseEntity::generateUUIDv4();
        $privileges = [
            Privilege::createNew(PrivilegeScope::canViewAllTimetables),
            Privilege::createNew(PrivilegeScope::canViewTimetableOfDepot, DatabaseEntity::generateUUIDv4())
        ];
        $profile = PersonnelProfile::createNew($user, $user, $description, $privileges);

        if (!is_a($profile, PersonnelProfile::class)) {
            return "Expected a ".PersonnelProfile::class." object. Found: ".gettype($profile).".";
        } elseif ($profile->getDescription() != $description) {
            return "Personnel profile description is incorrect. Expected: \"$description\", found: \"{$profile->getDescription()}\".";
        } elseif ($profile->getPrivileges() !== $privileges) {
            return "Personnel profile privileges array is incorrect. Expected: [$privileges[0], $privileges[1]], found: [".implode(", ", array_map(fn ($privilege) => (string) $privilege, $profile->getPrivileges()))."].";
        } elseif (is_null($profile->getActivatedAt())) {
            return "Personnel profile activatedAt value should not be null.";
        } elseif (!is_null($profile->getDeactivatedAt())) {
            return "New personnel profile deactivatedAt value should be null.";
        }

        return true;
    }

    public static function getPersonnelProfile(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $user->save();
        $userID = $user->getID();
        $description = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew(PrivilegeScope::canViewAllTimetables);
        $privilege->save();
        $privilegeID = $privilege->getID();
        $profile = PersonnelProfile::createNew($user, $user, $description, [$privilege]);
        $profile->save();
        $profileID = $profile->getID();
        unset($profile);
        $profile = PersonnelProfile::withID($profileID);

        $db = DatabaseConnector::shared();
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
                $profileID
            ]
        );
        $db->execute_query(
            "DELETE FROM profiles_personnel
            WHERE profile_id = ?",
            [
                $profileID
            ]
        );
        $db->execute_query(
            "DELETE FROM profiles
            WHERE id = ?",
            [
                $profileID
            ]
        );
        $db->execute_query(
            "DELETE FROM users
            WHERE id = ?",
            [
                $userID
            ]
        );

        if (!is_a($profile, PersonnelProfile::class)) {
            return "Expected a ".PersonnelProfile::class." object. Found: ".gettype($profile).".";
        } elseif ($profile->getDescription() != $description) {
            return "Personnel profile description is incorrect. Expected: \"$description\", found: \"{$profile->getDescription()}\".";
        } elseif (count($profile->getPrivileges()) != 1) {
            return "Personnel profile privileges count is incorrect. Expected: 1, found: ".count($profile->getPrivileges()).".";
        } elseif ($profile->getPrivileges()[0]->getID() != $privilegeID) {
            return "Personnel profile privilege ID is incorrect. Expected: \"$privilegeID\", found: \"{$profile->getPrivileges()[0]->getID()}\".";
        } elseif (is_null($profile->getActivatedAt())) {
            return "Personnel profile activatedAt value should not be null.";
        } elseif (!is_null($profile->getDeactivatedAt())) {
            return "Personnel profile deactivatedAt value should be null.";
        }

        return true;
    }

    public static function deactivatePersonnelProfile(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $description = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew(PrivilegeScope::canViewAllTimetables);
        $profile = PersonnelProfile::createNew($user, $user, $description, [$privilege]);
        $profile->deactivate($user);

        if (is_null($profile->getDeactivatedAt())) {
            return "Deactivated personnel profile deactivatedAt value should not be null.";
        } elseif (is_null($profile->getDeactivatedBy())) {
            return "Deactivated personnel profile deactivatedBy value should not be null.";
        } elseif ($profile->getDeactivatedBy()->getID() != $user->getID()) {
            return "Deactivated personnel profile deactivatedBy value is incorrect. Expected (userID): \"{$user->getID()}\", found (userID): \"{$profile->getDeactivatedBy()->getID()}\".";
        }

        return true;
    }

    public static function getPersonnelProfileHistoryForUser(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $user->save();
        $userID = $user->getID();
        $privilegeConfigurations = [
            [PrivilegeScope::canViewAllTimetables, null],
            [PrivilegeScope::canViewTimetableOfDepot, DatabaseEntity::generateUUIDv4()]
        ];
        $descriptions = ["roleA", "roleB"];
        $privileges = [
            Privilege::createNew($privilegeConfigurations[0][0], $privilegeConfigurations[0][1]),
            Privilege::createNew($privilegeConfigurations[1][0], $privilegeConfigurations[1][1])
        ];
        array_walk($privileges, fn ($privilege) => $privilege->save());
        $privilegeIDs = array_map(fn ($privilege) => $privilege->getID(), $privileges);
        $personnelProfile = PersonnelProfile::createNew($user, $user, $descriptions[0], [$privileges[0]]);
        $personnelProfile->deactivate($user);
        $personnelProfile->save();
        $personnelProfileIDs = [$personnelProfile->getID()];
        unset($personnelProfile);
        $personnelProfile = PersonnelProfile::createNew($user, $user, $descriptions[1], [$privileges[1]]);
        $personnelProfile->deactivate($user);
        $personnelProfile->save();
        $personnelProfileIDs[] = $personnelProfile->getID();
        unset($personnelProfile);
        unset($privileges);
        $history = PersonnelProfile::historyForUser($user);

        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM privileges
            WHERE id = ? OR id = ?",
            $privilegeIDs
        );
        $db->execute_query(
            "DELETE FROM personnel_profile_privileges
            WHERE personnel_profile_id = ? OR personnel_profile_id = ?",
            $personnelProfileIDs
        );
        $db->execute_query(
            "DELETE FROM profiles_personnel
            WHERE profile_id = ? OR profile_id = ?",
            $personnelProfileIDs
        );
        $db->execute_query(
            "DELETE FROM profiles
            WHERE id = ? OR id = ?",
            $personnelProfileIDs
        );
        $db->execute_query(
            "DELETE FROM users
            WHERE id = ?",
            [
                $userID
            ]
        );

        if (count($history) != 2) {
            return "The number of fetched personnel profiles for user is incorrect. Expected: 2, found: ".count($history).".";
        } elseif ($history[0]->getID() != $personnelProfileIDs[0]) {
            return "The first created personnel profile ID is incorrect. Expected: \"{$personnelProfileIDs[0]}\", found: \"{$history[0]->getID()}\".";
        } elseif ($history[1]->getID() != $personnelProfileIDs[1]) {
            return "The second created personnel profile ID is incorrect. Expected: \"{$personnelProfileIDs[1]}\", found: \"{$history[1]->getID()}\".";
        } elseif ($history[0]->getDescription() != $descriptions[0]) {
            return "The first created personnel profile description is incorrect. Expected: \"{$descriptions[0]}\", found: \"{$history[0]->getDescription()}\".";
        } elseif ($history[1]->getDescription() != $descriptions[1]) {
            return "The second created personnel profile description is incorrect. Expected: \"{$descriptions[1]}\", found: \"{$history[1]->getDescription()}\".";
        } elseif ($history[0]->getPrivileges()[0]->getScope() != $privilegeConfigurations[0][0]) {
            return "The first created personnel profile privilege scope is incorrect. Expected: {$privilegeConfigurations[0][0]->name}, found: {$history[0]->getPrivileges()[0]->getScope()->name}.";
        } elseif ($history[1]->getPrivileges()[0]->getScope() != $privilegeConfigurations[1][0]) {
            return "The second created personnel profile privilege scope is incorrect. Expected: {$privilegeConfigurations[1][0]->name}, found: {$history[1]->getPrivileges()[0]->getScope()->name}.";
        } elseif ($history[0]->getPrivileges()[0]->getAssociatedEntityID() !== $privilegeConfigurations[0][1]) {
            return "The first created personnel profile privilege associated entity ID is incorrect. Expected: null, found: \"{$history[0]->getPrivileges()[0]->getAssociatedEntityID()}\".";
        } elseif ($history[1]->getPrivileges()[0]->getAssociatedEntityID() !== $privilegeConfigurations[1][1]) {
            return "The second created personnel profile privilege associated entity ID is incorrect. Expected: \"{$privilegeConfigurations[1][1]}\", found: \"{$history[0]->getPrivileges()[0]->getAssociatedEntityID()}\".";
        } elseif (!$history[0]->getActivatedAt()->isBefore($history[1]->getActivatedAt())) {
            return "The first created personnel profile is not on the first position on the list.";
        }

        return true;
    }
}

?>