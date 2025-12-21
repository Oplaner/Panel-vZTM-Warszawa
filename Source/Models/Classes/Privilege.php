<?php

final class Privilege extends DatabaseEntity {
    private PrivilegeScope $scope;
    private ?AssociatedEntityType $associatedEntityType;
    private ?string $associatedEntityID;

    private function __construct(?string $id, PrivilegeScope $scope, ?AssociatedEntityType $associatedEntityType, ?string $associatedEntityID) {
        parent::__construct($id);
        $this->scope = $scope;
        $this->associatedEntityType = $associatedEntityType;
        $this->associatedEntityID = $associatedEntityID;
        $this->save();
    }

    public static function createNew(PrivilegeScope $scope, ?AssociatedEntityType $associatedEntityType = null, ?string $associatedEntityID = null): Privilege {
        Logger::log(LogLevel::info, "Creating new privilege with scope \"{$scope->value}\", associated entity type ".(is_null($associatedEntityType) ? "null" : "\"{$associatedEntityType->value}\"")." and ID ".(is_null($associatedEntityID) ? "null" : "\"$associatedEntityID\"").".");
        return new Privilege(null, $scope, $associatedEntityType, $associatedEntityID);
    }

    public static function withID(string $id): ?Privilege {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, Privilege::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT scope, associated_entity_type, associated_entity_id
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
        $scope = PrivilegeScope::from($data["scope"]);
        $associatedEntityType = is_null($data["associated_entity_type"]) ? null : AssociatedEntityType::from($data["associated_entity_type"]);
        $associatedEntityID = $data["associated_entity_id"];
        return new Privilege($id, $scope, $associatedEntityType, $associatedEntityID);
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

    public static function getGrantableGroups(): array {
        $privileges = array_filter(
            self::getAll(),
            fn($privilege) => $privilege->isGrantable()
        );
        usort($privileges, fn($a, $b) => self::comparePrivileges($a, $b));

        $groups = [];
        $currentGroupIndex = -1;
        $currentAssociatedEntityID = -1;

        foreach ($privileges as $privilege) {
            if ($privilege->associatedEntityID != $currentAssociatedEntityID) {
                $currentGroupIndex++;
            }

            $groups[$currentGroupIndex][] = $privilege;
            $currentAssociatedEntityID = $privilege->associatedEntityID;
        }

        return $groups;
    }

    private static function getAll(): array {
        $query =
            "SELECT id
            FROM privileges
            ORDER BY (associated_entity_type IS NULL) DESC, associated_entity_type ASC";
        return self::getWithQuery($query);
    }

    private static function comparePrivileges(Privilege $a, Privilege $b): int {
        if (is_null($a->associatedEntityType) && is_null($b->associatedEntityType)) {
            return strcmp($a->getDescription(), $b->getDescription());
        } elseif (is_null($a->associatedEntityType)) {
            return -1;
        } elseif (is_null($b->associatedEntityType)) {
            return 1;
        } elseif ($a->associatedEntityType != $b->associatedEntityType) {
            return strcmp($a->associatedEntityType->value, $b->associatedEntityType->value);
        } elseif ($a->associatedEntityID != $b->associatedEntityID) {
            $associatedEntityA = $a->associatedEntityType->getClass()::withID($a->associatedEntityID);
            $associatedEntityB = $b->associatedEntityType->getClass()::withID($b->associatedEntityID);
            return strcmp($associatedEntityA->getAssociatedEntityName(), $associatedEntityB->getAssociatedEntityName());
        } else {
            return strcmp($a->getDescription(), $b->getDescription());
        }
    }

    public function getScope(): PrivilegeScope {
        return $this->scope;
    }

    public function getAssociatedEntityType(): ?AssociatedEntityType {
        return $this->associatedEntityType;
    }

    public function getAssociatedEntityID(): ?string {
        return $this->associatedEntityID;
    }

    public function getDescription(): string {
        $associatedEntitySuffix = "";

        if (!is_null($this->associatedEntityType)) {
            $entity = $this->associatedEntityType->getClass()::withID($this->associatedEntityID);
            $associatedEntitySuffix = " ({$entity->getAssociatedEntityName()})";
        }

        return $this->scope->getDescription().$associatedEntitySuffix;
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", scope: \"%s\", associatedEntityType: %s, associatedEntityID: %s)",
            $this->id,
            $this->scope->value,
            is_null($this->associatedEntityType) ? "null" : "\"".$this->associatedEntityType->value."\"",
            is_null($this->associatedEntityID) ? "null" : "\"".$this->associatedEntityID."\""
        );
    }

    protected function save(): void {
        if ($this->isNew) {
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO privileges
                (id, scope, associated_entity_type, associated_entity_id)
                VALUES (?, ?, ?, ?)",
                [
                    $this->id,
                    $this->scope->value,
                    $this->associatedEntityType?->value,
                    $this->associatedEntityID
                ]
            );
            $this->isNew = false;
        }
    }

    private function isGrantable(): bool {
        if (is_null($this->associatedEntityType)) {
            return true;
        }

        $associatedEntity = $this->associatedEntityType->getClass()::withID($this->associatedEntityID);
        return $associatedEntity->isActive();
    }
}

?>