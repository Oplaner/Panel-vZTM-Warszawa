<?php

abstract class Profile extends DatabaseEntity {
    protected string $ownerID;
    protected SystemDateTime $activatedAt;
    protected User $activatedBy;
    protected ?SystemDateTime $deactivatedAt;
    protected ?User $deactivatedBy;

    protected function __construct(?string $id, string $ownerID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy) {
        parent::__construct($id);
        $this->ownerID = $ownerID;
        $this->activatedAt = $activatedAt;
        $this->activatedBy = $activatedBy;
        $this->deactivatedAt = $deactivatedAt;
        $this->deactivatedBy = $deactivatedBy;
    }

    public static function getAllByUser(User $user): array {
        $query =
            "SELECT id, type
            FROM profiles
            WHERE user_id = ?
            ORDER BY activated_at ASC";
        $parameters = [
            $user->getID()
        ];
        return self::getWithQuery($query, $parameters);
    }

    public static function getActiveByUser(User $user): array {
        $query =
            "SELECT id, type
            FROM profiles
            WHERE user_id = ?
            AND deactivated_at IS NULL
            ORDER BY activated_at ASC";
        $parameters = [
            $user->getID()
        ];
        return self::getWithQuery($query, $parameters);
    }

    public static function getAllByType(ProfileType $type, string $sortSubstring = "activated_at ASC"): array {
        $query =
            "SELECT id, type
            FROM profiles
            WHERE type = ?
            ORDER BY $sortSubstring";
        $parameters = [
            $type->value
        ];
        return self::getWithQuery($query, $parameters);
    }

    public static function getActiveByType(ProfileType $type, string $sortSubstring = "activated_at ASC"): array {
        $query =
            "SELECT id, type
            FROM profiles
            WHERE type = ?
            AND deactivated_at IS NULL
            ORDER BY $sortSubstring";
        $parameters = [
            $type->value
        ];
        return self::getWithQuery($query, $parameters);
    }

    protected static function validateUserDoesNotHaveProfileOfType(User $user): void {
        $profileType = static::class;

        if ($user->hasActiveProfileOfType($profileType)) {
            throw new DomainException("Cannot create new $profileType - there is one currently active for the user.");
        }
    }

    private static function getWithQuery(string $query, ?array $parameters = null): array {
        $result = DatabaseConnector::shared()->execute_query($query, $parameters);
        $profiles = [];

        while ($data = $result->fetch_assoc()) {
            $profileID = $data["id"];
            $profileTypeClass = ProfileType::from($data["type"])->getClass();
            $profiles[] = $profileTypeClass::withID($profileID);
        }

        $result->free();
        return $profiles;
    }

    public function getOwner(): User {
        return User::withID($this->ownerID);
    }

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
        if (!$this->isActive()) {
            throw new DomainException("Profile is already deactivated.");
        }

        Logger::log(LogLevel::info, "User with ID \"{$deactivator->getID()}\" is deactivating profile with ID \"{$this->id}\".");
        $this->deactivatedAt = SystemDateTime::now();
        $this->deactivatedBy = $deactivator;
        $this->wasModified = true;
        $this->save();
    }

    protected function saveNewProfileToDatabase(ProfileType $type): void {
        DatabaseConnector::shared()->execute_query(
            "INSERT INTO profiles
            (id, user_id, type, activated_at, activated_by_user_id, deactivated_at, deactivated_by_user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $this->id,
                $this->ownerID,
                $type->value,
                $this->activatedAt->toDatabaseString(),
                $this->activatedBy->getID(),
                null,
                null
            ]
        );
    }

    protected function saveExistingProfileToDatabase(): void {
        DatabaseConnector::shared()->execute_query(
            "UPDATE profiles
            SET deactivated_at = ?, deactivated_by_user_id = ?
            WHERE id = ?",
            [
                $this->deactivatedAt?->toDatabaseString(),
                $this->deactivatedBy?->getID(),
                $this->id
            ]
        );
    }
}

?>