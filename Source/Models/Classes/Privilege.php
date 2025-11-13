<?php

final class Privilege extends DatabaseEntity {
    private PrivilegeScope $scope;
    private ?string $associatedEntityID;

    private function __construct(?string $id, PrivilegeScope $scope, ?string $associatedEntityID) {
        parent::__construct($id);
        $this->scope = $scope;
        $this->associatedEntityID = $associatedEntityID;
        $this->save();
    }

    public static function createNew(PrivilegeScope $scope, ?string $associatedEntityID = null): Privilege {
        Logger::log(LogLevel::info, "Creating new privilege with scope \"{$scope->value}\" and associated entity ID ".(is_null($associatedEntityID) ? "null" : "\"$associatedEntityID\"").".");
        return new Privilege(null, $scope, $associatedEntityID);
    }

    public static function withID(string $id): ?Privilege {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, Privilege::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT scope, associated_entity_id
            FROM privileges
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find privilege with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        return new Privilege($id, PrivilegeScope::from($data["scope"]), $data["associated_entity_id"]);
    }

    public static function withScopeAndAssociatedEntityID(PrivilegeScope $scope, ?string $associatedEntityID): ?Privilege {
        $associatedEntityIDQueryString = "IS NULL";
        $parameters = [
            $scope->value
        ];

        if (!is_null($associatedEntityID)) {
            $associatedEntityIDQueryString = "= ?";
            $parameters[] = $associatedEntityID;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT id
            FROM privileges
            WHERE scope = ? AND associated_entity_id $associatedEntityIDQueryString",
            $parameters
        );

        if ($result->num_rows == 0) {
            $result->free();
            return null;
        }

        $id = $result->fetch_column();
        $result->free();
        return self::withID($id);
    }

    public function getScope(): PrivilegeScope {
        return $this->scope;
    }

    public function getAssociatedEntityID(): ?string {
        return $this->associatedEntityID;
    }

    public function getDescription(string $associatedEntityClass): string {
        $associatedEntitySuffix = "";

        if (!is_null($this->associatedEntityID)) {
            $entity = $associatedEntityClass::withID($this->associatedEntityID);
            $associatedEntitySuffix = " ({$entity->getAssociatedEntityName()})";
        }

        return $this->scope->getDescription().$associatedEntitySuffix;
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", scope: \"%s\", associatedEntityID: %s)",
            $this->id,
            $this->scope->value,
            is_null($this->associatedEntityID) ? "null" : "\"".$this->associatedEntityID."\""
        );
    }

    protected function save(): void {
        if ($this->isNew) {
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
            $this->isNew = false;
        }
    }
}

?>