<?php

final class PrivilegeTests {
    public static function createPrivilegeWithoutAssociatedEntityID(): bool|string {
        $scope = PrivilegeScope::canViewAllTimetables;
        $privilege = Privilege::createNew($scope);

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getScope() != $scope) {
            return "Privilege scope is incorrect. Expected: {$scope->name}, found: {$privilege->getScope()->name}.";
        } elseif (!is_null($privilege->getAssociatedEntityID())) {
            return "Privilege associatedEntityID is incorrect. Expected: null, found: \"{$privilege->getAssociatedEntityID()}\".";
        }

        return true;
    }

    public static function createPrivilegeWithAssociatedEntity(): bool|string {
        $scope = PrivilegeScope::canViewTimetableOfDepot;
        $associatedEntityType = AssociatedEntityType::carrier;
        $associatedEntityID = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew($scope, $associatedEntityType, $associatedEntityID);

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getScope() != $scope) {
            return "Privilege scope is incorrect. Expected: {$scope->name}, found: {$privilege->getScope()->name}.";
        } elseif ($privilege->getAssociatedEntityType() != $associatedEntityType) {
            return "Privilege associatedEntityType is incorrect. Expected: {$associatedEntityType->name}, found: {$privilege->getAssociatedEntityType()->name}.";
        } elseif (is_null($privilege->getAssociatedEntityID())) {
            return "Privilege associatedEntityID is incorrect. Expected: \"$associatedEntityID\", found: \"{$privilege->getAssociatedEntityID()}\".";
        }

        return true;
    }

    public static function getPrivilege(): bool|string {
        $scope = PrivilegeScope::canViewTimetableOfDepot;
        $associatedEntityType = AssociatedEntityType::carrier;
        $associatedEntityID = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew($scope, $associatedEntityType, $associatedEntityID);
        DatabaseEntity::removeFromCache($privilege);
        $privilege = Privilege::withID($privilege->getID());

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getScope() != $scope) {
            return "Privilege scope is incorrect. Expected: {$scope->name}, found: {$privilege->getScope()->name}.";
        } elseif ($privilege->getAssociatedEntityType() != $associatedEntityType) {
            return "Privilege associatedEntityType is incorrect. Expected: {$associatedEntityType->name}, found: {$privilege->getAssociatedEntityType()->name}.";
        } elseif ($privilege->getAssociatedEntityID() != $associatedEntityID) {
            return "Privilege associatedEntityID is incorrect. Expected: \"$associatedEntityID\", found: \"{$privilege->getAssociatedEntityID()}\".";
        }

        return true;
    }

    public static function getPrivilegeByScope(): bool|string {
        $scope = PrivilegeScope::canViewAllTimetables;
        $privilege = Privilege::createNew($scope);
        $privilegeID = $privilege->getID();
        $privilege = Privilege::withScopeAndAssociatedEntityID($scope, null);

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getID() != $privilegeID) {
            return "Privilege ID is incorrect. Expected: \"$privilegeID\", found: \"{$privilege->getID()}\".";
        }

        return true;
    }

    public static function getPrivilegeByScopeAndAssociatedEntityID(): bool|string {
        $scope = PrivilegeScope::canViewTimetableOfDepot;
        $associatedEntityType = AssociatedEntityType::carrier;
        $associatedEntityID = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew($scope, $associatedEntityType, $associatedEntityID);
        $privilegeID = $privilege->getID();
        $privilege = Privilege::withScopeAndAssociatedEntityID($scope, $associatedEntityID);

        if (!is_a($privilege, Privilege::class)) {
            return "Expected a ".Privilege::class." object. Found: ".gettype($privilege).".";
        } elseif ($privilege->getID() != $privilegeID) {
            return "Privilege ID is incorrect. Expected: \"$privilegeID\", found: \"{$privilege->getID()}\".";
        }

        return true;
    }
}

?>