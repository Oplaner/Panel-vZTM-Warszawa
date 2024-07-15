<?php

final class PrivilegeSetTests {
    public static function throwExceptionWhenCreatingPrivilegeSetWithoutPrivileges(): bool|string {
        try {
            PrivilegeSet::createNew(DatabaseEntity::generateUUIDv4(), []);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when creating a privilege set without privileges.";
    }

    public static function createNewPrivilegeSet(): bool|string {
        $profileID = DatabaseEntity::generateUUIDv4();
        $privilege1 = Privilege::createNew(PrivilegeScope::canViewAllTimetables);
        $privilege2 = Privilege::createNew(PrivilegeScope::canViewTimetableOfDepot, DatabaseEntity::generateUUIDv4());
        $privilegeSet = PrivilegeSet::createNew($profileID, [$privilege1, $privilege2]);

        if (!is_a($privilegeSet, PrivilegeSet::class)) {
            return "Expected a ".PrivilegeSet::class." object. Found: ".gettype($privilegeSet).".";
        } elseif ($privilegeSet->getPrivileges() !== [$privilege1, $privilege2]) {
            return "Privilege set privileges array is incorrect. Expected: [$privilege1, $privilege2], found: [".implode(", ", array_map(fn ($privilege) => (string) $privilege, $privilegeSet->getPrivileges()))."].";
        } elseif (is_null($privilegeSet->getValidFrom())) {
            return "Privilege set validFrom value should not be null.";
        } elseif (!is_null($privilegeSet->getValidTo())) {
            return "New privilege set validTo value should be null.";
        }

        return true;
    }

    public static function getPrivilegeSet(): bool|string {
        $profileID = DatabaseEntity::generateUUIDv4();
        $privilege = Privilege::createNew(PrivilegeScope::canViewAllTimetables);
        $privilege->save();
        $privilegeID = $privilege->getID();
        $privilegeSet = PrivilegeSet::createNew($profileID, [$privilege]);
        $privilegeSet->save();
        $privilegeSetID = $privilegeSet->getID();
        unset($privilegeSet);
        $privilegeSet = PrivilegeSet::withID($privilegeSetID);

        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM privileges
            WHERE id = ?",
            [
                $privilegeID
            ]
        );
        $db->execute_query(
            "DELETE FROM privilege_set_privileges
            WHERE privilege_set_id = ?",
            [
                $privilegeSetID
            ]
        );
        $db->execute_query(
            "DELETE FROM privilege_sets
            WHERE id = ?",
            [
                $privilegeSetID
            ]
        );

        if (!is_a($privilegeSet, PrivilegeSet::class)) {
            return "Expected a ".PrivilegeSet::class." object. Found: ".gettype($privilegeSet).".";
        } elseif (count($privilegeSet->getPrivileges()) != 1) {
            return "Privilege set privileges count is incorrect. Expected: 1, found: ".count($privilegeSet->getPrivileges()).".";
        } elseif ($privilegeSet->getPrivileges()[0]->getID() != $privilegeID) {
            return "Privilege set privilege ID is incorrect. Expected: \"$privilegeID\", found: \"{$privilegeSet->getPrivileges()[0]->getID()}\".";
        } elseif (is_null($privilegeSet->getValidFrom())) {
            return "Privilege set validFrom value should not be null.";
        } elseif (!is_null($privilegeSet->getValidTo())) {
            return "Privilege set validTo value should be null.";
        }

        return true;
    }
}

?>