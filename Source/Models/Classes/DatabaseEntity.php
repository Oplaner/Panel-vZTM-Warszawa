<?php

abstract class DatabaseEntity {
    protected string $id;
    protected bool $isNew;

    final protected function __construct(?string $id = null) {
        if ($id === null) {
            $this->id = self::generateUUIDv4();
            $this->isNew = true;
        } else {
            $this->id = $id;
            $this->isNew = false;
        }
    }

    abstract public static function createNew(): DatabaseEntity;
    abstract public static function withID(string $id): ?DatabaseEntity;
    abstract public function save(): bool;

    private static function generateUUIDv4(): string {
        $randomData = random_bytes(16);
        $randomData[6] = chr(ord($randomData[6]) & 0x0F | 0x40);
        $randomData[8] = chr(ord($randomData[8]) & 0x3F | 0x80);
        $randomData = bin2hex($randomData);
        return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split($randomData, 4));
    }

    final public function getID(): string {
        return $this->id;
    }
}

?>