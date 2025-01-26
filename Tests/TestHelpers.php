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

    public static function deleteTestUser(string $userID): void {
        DatabaseConnector::shared()->execute_query(
            "DELETE FROM users
            WHERE login = ?",
            [
                self::EXISTING_TEST_USER_LOGIN
            ]
        );
    }

    public static function deleteTestCarrierData(string $carrierID): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM carrier_supervisors
            WHERE carrier_id = ?",
            [
                $carrierID
            ]
        );
        $db->execute_query(
            "DELETE FROM carriers
            WHERE id = ?",
            [
                $carrierID
            ]
        );
    }

    public static function deleteTestPrivilege(string $privilegeID): void {
        DatabaseConnector::shared()->execute_query(
            "DELETE FROM privileges
            WHERE id = ?",
            [
                $privilegeID
            ]
        );
    }

    public static function deleteTestPersonnelProfileData(string $profileID): void {
        $db = DatabaseConnector::shared();
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
    }

    public static function deleteTestDirectorProfileData(string $profileID): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM profiles_director
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
    }

    public static function deleteTestDriverProfile(string $profileID): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM profiles_driver
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
    }

    public static function deleteAllTestDriverProfiles(): void {
        $db = DatabaseConnector::shared();
        $db->execute_query("TRUNCATE TABLE profiles_driver");
        $db->execute_query(
            "DELETE FROM profiles
            WHERE type = ?",
            [
                "DRIVER"
            ]
        );
    }

    public static function deleteTestContractData(string $contractID): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM contract_periods
            WHERE contract_id = ?",
            [
                $contractID
            ]
        );
        $db->execute_query(
            "DELETE FROM contracts
            WHERE id = ?",
            [
                $contractID
            ]
        );
    }
}

?>