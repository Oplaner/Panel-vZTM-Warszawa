<?php

final class Privilege extends DatabaseEntity {
    private PrivilegeScope $scope;
    private ?string $associatedEntityID;

    private function __construct(?string $id, PrivilegeScope $scope, ?string $associatedEntityID) {
        parent::__construct($id);
        $this->scope = $scope;
        $this->associatedEntityID = $associatedEntityID;
    }

    public static function createNew(PrivilegeScope $scope, ?string $associatedEntityID = null): Privilege {
        Logger::log(LogLevel::info, "Creating new privilege with scope \"{$scope->value}\" and associated entity ID ".(is_null($associatedEntityID) ? "null" : "\"$associatedEntityID\"").".");
        return new Privilege(null, $scope, $associatedEntityID);
    }

    public static function withID(string $id): ?Privilege {
        Logger::log(LogLevel::info, "Fetching privilege with ID \"$id\".");
        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT scope, associated_entity_id
            FROM privileges
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find privilege with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $privilege = new Privilege($id, PrivilegeScope::from($data["scope"]), $data["associated_entity_id"]);
        Logger::log(LogLevel::info, "Fetched privilege: $privilege.");
        return $privilege;
    }

    public function getScope(): PrivilegeScope {
        return $this->scope;
    }

    public function getAssociatedEntityID(): ?string {
        return $this->associatedEntityID;
    }

    public function save(): void {
        if ($this->isNew) {
            Logger::log(LogLevel::info, "Saving new privilege: $this.");
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO privileges
                (id, scope, associated_entity_id)
                VALUES (?, ?, ?)",
                [
                    $this->id,
                    $this->scope->value,
                    $this->associatedEntityID
                ]
            );
        }
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", scope: \"%s\", associatedEntityID: %s)",
            $this->id,
            $this->scope->value,
            is_null($this->associatedEntityID) ? "null" : "\"".$this->associatedEntityID."\""
        );
    }
}

?>