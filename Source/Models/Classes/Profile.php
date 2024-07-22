<?php

abstract class Profile extends DatabaseEntity {
    protected string $userID;
    protected SystemDateTime $activatedAt;
    protected User $activatedBy;
    protected ?SystemDateTime $deactivatedAt;
    protected ?User $deactivatedBy;

    protected function __construct(?string $id, string $userID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy) {
        parent::__construct($id);
        $this->userID = $userID;
        $this->activatedAt = $activatedAt;
        $this->activatedBy = $activatedBy;
        $this->deactivatedAt = $deactivatedAt;
        $this->deactivatedBy = $deactivatedBy;
    }

    // TODO: Get profiles for a user (different types, active).

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

    protected function saveNewProfileToDatabase(string $profileType): void {
        DatabaseConnector::shared()->execute_query(
            "INSERT INTO profiles
            (id, user_id, type, activated_at, activated_by_user_id, deactivated_at, deactivated_by_user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $this->id,
                $this->userID,
                $profileType,
                $this->activatedAt->toDatabaseString(),
                $this->activatedBy->getID(),
                is_null($this->deactivatedAt) ? null : $this->deactivatedAt->toDatabaseString(),
                is_null($this->deactivatedBy) ? null : $this->deactivatedBy->getID()
            ]
        );
    }

    protected function saveExistingProfileToDatabase(): void {
        DatabaseConnector::shared()->execute_query(
            "UPDATE profiles
            SET deactivated_at = ?, deactivated_by_user_id = ?
            WHERE id = ?",
            [
                $this->deactivatedAt->toDatabaseString(),
                $this->deactivatedBy->getID(),
                $this->id
            ]
        );
    }
}

?>