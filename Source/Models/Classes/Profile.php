<?php

abstract class Profile extends DatabaseEntity {
    protected const DATABASE_PROFILE_TYPE_DIRECTOR = "DIRECTOR";
    protected const DATABASE_PROFILE_TYPE_DRIVER = "DRIVER";
    protected const DATABASE_PROFILE_TYPE_PERSONNEL = "PERSONNEL";

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

    public static function getAllProfilesOfUser(User $user): array {
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT id, type
            FROM profiles
            WHERE user_id = ?
            ORDER BY activated_at ASC",
            [
                $user->getID()
            ]
        );
        $profiles = [];

        while ($data = $result->fetch_assoc()) {
            $profileID = $data["id"];
            $profileType = $data["type"];
            $profiles[] = self::getProfileWithIDAndType($profileID, $profileType);
        }

        $result->free();
        return $profiles;
    }

    private static function getProfileWithIDAndType(string $profileID, string $profileType): ?Profile {
        switch ($profileType) {
            case self::DATABASE_PROFILE_TYPE_DIRECTOR:
                return DirectorProfile::withID($profileID);
            case self::DATABASE_PROFILE_TYPE_DRIVER:
                return DriverProfile::withID($profileID);
            case self::DATABASE_PROFILE_TYPE_PERSONNEL:
                return PersonnelProfile::withID($profileID);
            default:
                Logger::log(LogLevel::error, "Unexpected profile type \"$profileType\" for profile with ID \"$profileID\".");
                return null;
        }
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
        Logger::log(LogLevel::info, "User with ID \"{$deactivator->getID()}\" is deactivating profile with ID \"{$this->id}\".");
        $this->deactivatedAt = SystemDateTime::now();
        $this->deactivatedBy = $deactivator;
        $this->wasModified = true;
        $this->save();
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