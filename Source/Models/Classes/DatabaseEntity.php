<?php

abstract class DatabaseEntity {
    private static array $cachedEntities = [];

    protected string $id;
    protected bool $isNew;
    protected bool $wasModified = false;

    protected function __construct(?string $id) {
        $this->setID($id);
        self::$cachedEntities[] = $this;
    }

    public static abstract function withID(string $id): ?DatabaseEntity;

    public static function generateUUIDv4(): string {
        $randomData = random_bytes(16);
        $randomData[6] = chr(ord($randomData[6]) & 0x0F | 0x40);
        $randomData[8] = chr(ord($randomData[8]) & 0x3F | 0x80);
        $randomData = bin2hex($randomData);
        return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split($randomData, 4));
    }

    public static function removeFromCache(DatabaseEntity $entity): void {
        for ($i = 0; $i < count(self::$cachedEntities); $i++) {
            if (self::$cachedEntities[$i]->id == $entity->id) {
                array_splice(self::$cachedEntities, $i, 1);
                return;
            }
        }
    }

    protected static function findCached(string $entityID): ?DatabaseEntity {
        foreach (self::$cachedEntities as $entity) {
            if ($entity->id == $entityID) {
                return $entity;
            }
        }

        return null;
    }

    protected static function getWithQuery(string $query, ?array $parameters = null): array {
        $result = DatabaseConnector::shared()->execute_query($query, $parameters);
        $entities = [];

        while ($entityID = $result->fetch_column()) {
            $entities[] = static::withID($entityID);
        }

        $result->free();
        return $entities;
    }

    protected static function getCountWithQuery(string $query, ?array $parameters = null): int {
        $result = DatabaseConnector::shared()->execute_query($query, $parameters);
        $count = $result->fetch_column();
        $result->free();
        return $count;
    }

    protected static function makeQueryLimitString(?string $limitSubstring): string {
        return is_null($limitSubstring) ? "" : "LIMIT $limitSubstring";
    }

    public function getID(): string {
        return $this->id;
    }

    protected abstract function save(): void;

    private function setID(?string $id): void {
        if (is_null($id)) {
            $this->id = self::generateUUIDv4();
            $this->isNew = true;
        } else {
            $this->id = $id;
            $this->isNew = false;
        }
    }
}

?>