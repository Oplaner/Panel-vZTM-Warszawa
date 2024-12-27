<?php

final class PrivilegeTests {
    public static function createPrivilegeWithoutAssociatedEntityID(): bool|string {
        $scope = PrivilegeScope::canViewAllTimetables;
        $privilege = Privilege::createNew($scope);

        TestHelpers::deleteTestPrivilege($privilege->getID());

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getScope() != $scope) {
            return "Privilege scope is incorrect. Expected: {$scope->name}, found: {$privilege->getScope()->name}.";
        } elseif (!is_null($privilege->getAssociatedEntityID())) {
            return "Privilege associatedEntityID is incorrect. Expected: null, found: \"{$privilege->getAssociatedEntityID()}\".";
        }

        return true;
    }

    public static function createPrivilegeWithAssociatedEntityID(): bool|string {
        $scope = PrivilegeScope::canViewTimetableOfDepot;
        $associatedEntityID = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew($scope, $associatedEntityID);

        TestHelpers::deleteTestPrivilege($privilege->getID());

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getScope() != $scope) {
            return "Privilege scope is incorrect. Expected: {$scope->name}, found: {$privilege->getScope()->name}.";
        } elseif (is_null($privilege->getAssociatedEntityID())) {
            return "Privilege associatedEntityID is incorrect. Expected: \"$associatedEntityID\", found: \"{$privilege->getAssociatedEntityID()}\".";
        }

        return true;
    }

    public static function getPrivilege(): bool|string {
        $scope = PrivilegeScope::canViewTimetableOfDepot;
        $associatedEntityID = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew($scope, $associatedEntityID);
        DatabaseEntity::removeFromCache($privilege);
        $privilege = Privilege::withID($privilege->getID());

        TestHelpers::deleteTestPrivilege($privilege->getID());

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getScope() != $scope) {
            return "Privilege scope is incorrect. Expected: {$scope->name}, found: {$privilege->getScope()->name}.";
        } elseif ($privilege->getAssociatedEntityID() != $associatedEntityID) {
            return "Privilege associatedEntityID is incorrect. Expected: \"$associatedEntityID\", found: \"{$privilege->getAssociatedEntityID()}\".";
        }

        return true;
    }
}

?>