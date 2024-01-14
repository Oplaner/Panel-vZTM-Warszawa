<?php

require_once __DIR__."/Profile.php";
require_once __DIR__."/SystemDateTime.php";
require_once __DIR__."/User.php";

final class DirectorProfile extends Profile {
    private bool $isProtected;

    private function __construct(?string $id, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy, bool $isProtected) {
        $this->setID($id);
        $this->activatedAt = $activatedAt;
        $this->activatedBy = $activatedBy;
        $this->deactivatedAt = $deactivatedAt;
        $this->deactivatedBy = $deactivatedBy;
        $this->isProtected = $isProtected;
    }

    public static function createNew(User $activator, bool $isProtected): DirectorProfile {
        return new DirectorProfile(null, SystemDateTime::now(), $activator, null, null, $isProtected);
    }

    public static function withID(string $id): ?DirectorProfile {
        // TODO: Get profile from database. If not found, return null.
        return null;
    }

    public function isProtected(): bool {
        return $this->isProtected;
    }

    public function save(): bool {
        // Try to save this entity to database.
        return true;
    }
}

?>