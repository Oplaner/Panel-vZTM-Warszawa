<?php

require_once __DIR__."/DatabaseEntity.php";
require_once __DIR__."/SystemDateTime.php";
require_once __DIR__."/User.php";

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
        return is_null($this->deactivatedAt);
    }

    public function deactivate(User $deactivator): void {
        $this->deactivatedAt = SystemDateTime::now();
        $this->deactivatedBy = $deactivator;
        $this->wasModified = true;
    }
}

?>