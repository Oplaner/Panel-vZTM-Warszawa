<?php

require_once "DatabaseEntity.php";
require_once "SystemDateTime.php";
require_once "User.php";

abstract class Profile extends DatabaseEntity {
    protected SystemDateTime $activatedAt;
    protected User $activatedBy;
    protected ?SystemDateTime $deactivatedAt = null;
    protected ?User $deactivatedBy = null;

    public function getActivatedAt(): SystemDateTime {
        return $this->activatedAt;
    }

    public function getActivatedBy(): User {
        return $this->activatedBy;
    }

    public function getDeactivatedAt(): ?SystemDateTime {
        return $this->deactivatedAt;
    }

    public function getDeactivatedBy(): ?User {
        return $this->deactivatedBy;
    }

    public function isActive(): bool {
        return $this->deactivatedAt === null;
    }

    public function deactivate(User $deactivator): void {
        $this->deactivatedAt = SystemDateTime::now();
        $this->deactivatedBy = $deactivator;
        $this->wasModified = true;
    }
}

?>