<?php

final class PersonnelProfileTests {
    public static function throwExceptionWhenCreatingPersonnelProfileWithoutPrivileges(): bool|string {
        $user = TestHelpers::createTestUser();
        TestHelpers::deleteTestUser($user->getID());

        try {
            PersonnelProfile::createNew($user, $user, "", []);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when creating a personnel profile without privileges.";
    }

    public static function createNewPersonnelProfile(): bool|string {
        $user = TestHelpers::createTestUser();
        $description = DatabaseEntity::generateUUIDv4();
        $privileges = [
            TestHelpers::createTestPrivilege(),
            TestHelpers::createTestPrivilegeWithAssociatedEntity()
        ];
        $profile = PersonnelProfile::createNew($user, $user, $description, $privileges);

        TestHelpers::deleteTestPersonnelProfileData($profile->getID());
        TestHelpers::deleteTestPrivilege($privileges[0]->getID());
        TestHelpers::deleteTestPrivilege($privileges[1]->getID());
        TestHelpers::deleteTestUser($user->getID());

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
        $user = TestHelpers::createTestUser();
        $description = DatabaseEntity::generateUUIDv4();
        $privilege = TestHelpers::createTestPrivilege();
        $profile = PersonnelProfile::createNew($user, $user, $description, [$privilege]);
        DatabaseEntity::removeFromCache($profile);
        $profile = PersonnelProfile::withID($profile->getID());

        TestHelpers::deleteTestPersonnelProfileData($profile->getID());
        TestHelpers::deleteTestPrivilege($privilege->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile, PersonnelProfile::class)) {
            return "Expected a ".PersonnelProfile::class." object. Found: ".gettype($profile).".";
        } elseif ($profile->getDescription() != $description) {
            return "Personnel profile description is incorrect. Expected: \"$description\", found: \"{$profile->getDescription()}\".";
        } elseif (count($profile->getPrivileges()) != 1) {
            return "Personnel profile privileges count is incorrect. Expected: 1, found: ".count($profile->getPrivileges()).".";
        } elseif ($profile->getPrivileges()[0]->getID() != $privilege->getID()) {
            return "Personnel profile privilege ID is incorrect. Expected: \"{$privilege->getID()}\", found: \"{$profile->getPrivileges()[0]->getID()}\".";
        } elseif (is_null($profile->getActivatedAt())) {
            return "Personnel profile activatedAt value should not be null.";
        } elseif (!is_null($profile->getDeactivatedAt())) {
            return "Personnel profile deactivatedAt value should be null.";
        }

        return true;
    }

    public static function deactivatePersonnelProfile(): bool|string {
        $user = TestHelpers::createTestUser();
        $description = DatabaseEntity::generateUUIDv4();
        $privilege = TestHelpers::createTestPrivilege();
        $profile = PersonnelProfile::createNew($user, $user, $description, [$privilege]);
        $profile->deactivate($user);

        TestHelpers::deleteTestPersonnelProfileData($profile->getID());
        TestHelpers::deleteTestPrivilege($privilege->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (is_null($profile->getDeactivatedAt())) {
            return "Deactivated personnel profile deactivatedAt value should not be null.";
        } elseif (is_null($profile->getDeactivatedBy())) {
            return "Deactivated personnel profile deactivatedBy value should not be null.";
        } elseif ($profile->getDeactivatedBy()->getID() != $user->getID()) {
            return "Deactivated personnel profile deactivatedBy value is incorrect. Expected (userID): \"{$user->getID()}\", found (userID): \"{$profile->getDeactivatedBy()->getID()}\".";
        }

        return true;
    }

    public static function getUserPersonnelProfiles(): bool|string {
        $user = TestHelpers::createTestUser();
        $descriptions = ["roleA", "roleB"];
        $privileges = [
            TestHelpers::createTestPrivilege(),
            TestHelpers::createTestPrivilegeWithAssociatedEntity()
        ];
        $personnelProfile = PersonnelProfile::createNew($user, $user, $descriptions[0], [$privileges[0]]);
        $personnelProfile->deactivate($user);
        $personnelProfiles = [$personnelProfile];
        DatabaseEntity::removeFromCache($personnelProfile);
        $personnelProfile = PersonnelProfile::createNew($user, $user, $descriptions[1], [$privileges[1]]);
        $personnelProfile->deactivate($user);
        $personnelProfiles[] = $personnelProfile;
        DatabaseEntity::removeFromCache($personnelProfile);
        $profiles = PersonnelProfile::getAllByUser($user);

        TestHelpers::deleteTestPersonnelProfileData($personnelProfiles[0]->getID());
        TestHelpers::deleteTestPersonnelProfileData($personnelProfiles[1]->getID());
        TestHelpers::deleteTestPrivilege($privileges[0]->getID());
        TestHelpers::deleteTestPrivilege($privileges[1]->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (count($profiles) != 2) {
            return "The number of fetched personnel profiles for user is incorrect. Expected: 2, found: ".count($profiles).".";
        } elseif ($profiles[0]->getID() != $personnelProfiles[0]->getID()) {
            return "The first created personnel profile ID is incorrect. Expected: \"{$personnelProfiles[0]->getID()}\", found: \"{$profiles[0]->getID()}\".";
        } elseif ($profiles[1]->getID() != $personnelProfiles[1]->getID()) {
            return "The second created personnel profile ID is incorrect. Expected: \"{$personnelProfiles[1]->getID()}\", found: \"{$profiles[1]->getID()}\".";
        } elseif ($profiles[0]->getDescription() != $descriptions[0]) {
            return "The first created personnel profile description is incorrect. Expected: \"{$descriptions[0]}\", found: \"{$profiles[0]->getDescription()}\".";
        } elseif ($profiles[1]->getDescription() != $descriptions[1]) {
            return "The second created personnel profile description is incorrect. Expected: \"{$descriptions[1]}\", found: \"{$profiles[1]->getDescription()}\".";
        } elseif ($profiles[0]->getPrivileges()[0]->getScope() != $privileges[0]->getScope()) {
            return "The first created personnel profile privilege scope is incorrect. Expected: {$privileges[0]->getScope()->name}, found: {$profiles[0]->getPrivileges()[0]->getScope()->name}.";
        } elseif ($profiles[1]->getPrivileges()[0]->getScope() != $privileges[1]->getScope()) {
            return "The second created personnel profile privilege scope is incorrect. Expected: {$privileges[1]->getScope()->name}, found: {$profiles[1]->getPrivileges()[0]->getScope()->name}.";
        } elseif ($profiles[0]->getPrivileges()[0]->getAssociatedEntityID() !== $privileges[0]->getAssociatedEntityID()) {
            return "The first created personnel profile privilege associated entity ID is incorrect. Expected: null, found: \"{$profiles[0]->getPrivileges()[0]->getAssociatedEntityID()}\".";
        } elseif ($profiles[1]->getPrivileges()[0]->getAssociatedEntityID() !== $privileges[1]->getAssociatedEntityID()) {
            return "The second created personnel profile privilege associated entity ID is incorrect. Expected: \"{$privileges[1]->getAssociatedEntityID()}\", found: \"{$profiles[0]->getPrivileges()[0]->getAssociatedEntityID()}\".";
        } elseif (!$profiles[0]->getActivatedAt()->isBefore($profiles[1]->getActivatedAt())) {
            return "The first created personnel profile is not on the first position on the list.";
        }

        return true;
    }
}

?>