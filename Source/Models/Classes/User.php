<?php

require("DatabaseEntity.php");

class User extends DatabaseEntity {
    public static function createNew(): User {
        return new User();
    }

    public static function withID(string $id): ?User {
        $user = new User($id);
        return $user;
    }

    public function save(): bool {
        return true;
    }
}

?>