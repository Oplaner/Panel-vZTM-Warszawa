<?php

final class TestHelpers {
    public const EXISTING_TEST_USER_LOGIN = 1387;
    public const EXISTING_TEST_USER_USERNAME = "Oplaner";
    public const NOT_EXISTING_TEST_USER_LOGIN = 100;

    public static function createTestUser(): User {
        return User::createNew(self::EXISTING_TEST_USER_LOGIN);
    }

    public static function createTestCarrier(User $creator): Carrier {
        return Carrier::createNew("Test Carrier", "Test", [], 3, 5, $creator);
    }

    public static function createTestPrivilege(): Privilege {
        return Privilege::createNew(PrivilegeScope::canViewAllTimetables);
    }

    public static function createTestPrivilegeWithAssociatedEntity(): Privilege {
        return Privilege::createNew(PrivilegeScope::canViewTimetableOfDepot, DatabaseEntity::generateUUIDv4());
    }

    public static function createTestPersonnelProfile(User $user): PersonnelProfile {
        $privilege = self::createTestPrivilege();
        return PersonnelProfile::createNew($user, $user, "Test PersonnelProfile", [$privilege]);
    }

    public static function createTestDirectorProfile(User $user): DirectorProfile {
        return DirectorProfile::createNew($user, $user);
    }

    public static function createTestInactiveDriverProfileWithAcquiredPenalty(User $user): DriverProfile {
        $profile = DriverProfile::createNew($user, $user);
        $profile->incrementPenaltyMultiplier();
        $profile->deactivate($user);
        return $profile;
    }

    public static function cleanDatabase(): void {
        $tables = [
            "carriers",
            "carrier_supervisors",
            "contracts",
            "contract_periods",
            "personnel_profile_privileges",
            "privileges",
            "profiles",
            "profiles_director",
            "profiles_driver",
            "profiles_personnel",
            "session_tokens",
            "users"
        ];
        $queries = array_map(
            fn($table) => "TRUNCATE TABLE $table",
            $tables
        );
        array_walk(
            $queries,
            fn($query) => DatabaseConnector::shared()->query($query)
        );
    }
}

?>