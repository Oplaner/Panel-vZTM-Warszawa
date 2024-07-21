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
        $privilege1 = Privilege::createNew(PrivilegeScope::canViewAllTimetables);
        $privilege2 = Privilege::createNew(PrivilegeScope::canViewTimetableOfDepot, DatabaseEntity::generateUUIDv4());
        $profile = PersonnelProfile::createNew($user, $user, $description, [$privilege1, $privilege2]);

        if (!is_a($profile, PersonnelProfile::class)) {
            return "Expected a ".PersonnelProfile::class." object. Found: ".gettype($profile).".";
        } elseif ($profile->getDescription() != $description) {
            return "Personnel profile description is incorrect. Expected: \"$description\", found: \"{$profile->getDescription()}\".";
        } elseif ($profile->getPrivileges() !== [$privilege1, $privilege2]) {
            return "Personnel profile privileges array is incorrect. Expected: [$privilege1, $privilege2], found: [".implode(", ", array_map(fn ($privilege) => (string) $privilege, $profile->getPrivileges()))."].";
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
}

?>