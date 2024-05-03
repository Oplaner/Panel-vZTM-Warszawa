<?php

require_once __DIR__."/DatabaseConnector.php";
require_once __DIR__."/DatabaseEntity.php";
require_once __DIR__."/Logger.php";
require_once __DIR__."/../Enums/LogLevel.php";
require_once __DIR__."/../Enums/PrivilegeScope.php";

final class Privilege extends DatabaseEntity {
    private PrivilegeScope $scope;
    private ?string $associatedEntityID;

    private function __construct(?string $id, PrivilegeScope $scope, ?string $associatedEntityID) {
        $this->setID($id);
        $this->scope = $scope;
        $this->associatedEntityID = $associatedEntityID;
    }

    public static function createNew(PrivilegeScope $scope, ?string $associatedEntityID = null): Privilege {
        return new Privilege(null, $scope, $associatedEntityID);
    }

    public static function withID(string $id): ?Privilege {
        Logger::log(LogLevel::info, "Fetching privilege with ID \"$id\".");
        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT scope, associatedEntityID
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
        $privilege = new Privilege($id, PrivilegeScope::from($data["scope"]), $data["associatedEntityID"]);
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
                (id, scope, associatedEntityID)
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