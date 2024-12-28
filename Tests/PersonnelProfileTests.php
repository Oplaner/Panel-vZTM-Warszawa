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
        } elseif (!$profile->isActive()) {
            return "New personnel profile should be active.";
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
        } elseif ($profile->isActive()) {
            return "Deactivated personnel profile should be inactive.";
        }

        return true;
    }
}

?>