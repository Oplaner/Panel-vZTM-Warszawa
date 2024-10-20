<?php

abstract class Profile extends DatabaseEntity {
    protected const DATABASE_PROFILE_TYPE_DIRECTOR = "DIRECTOR";
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

    public static function getActiveProfilesForUser(User $user): array {
        Logger::log(LogLevel::info, "Fetching active profiles for user with ID \"{$user->getID()}\".");

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT id, type
            FROM profiles
            WHERE user_id = ? AND deactivated_at IS NULL
            ORDER BY activated_at ASC",
            [
                $user->getID()
            ]
        );

        $profiles = [];

        while ($data = $result->fetch_assoc()) {
            $profileID = $data["id"];
            $profileType = $data["type"];
            $profile = self::getProfileWithIDAndType($profileID, $profileType);

            if (!is_null($profile)) {
                $profiles[] = $profile;
            }
        }

        $result->free();
        Logger::log(LogLevel::info, "Found ".count($profiles)." active profile(s) for user with ID \"{$user->getID()}\".");
        return $profiles;
    }

    private static function getProfileWithIDAndType(string $profileID, string $profileType): ?Profile {
        switch ($profileType) {
            case self::DATABASE_PROFILE_TYPE_DIRECTOR:
                return DirectorProfile::withID($profileID);
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
        Logger::log(LogLevel::info, "User with ID \"{$deactivator->getID()}\" is deactivating profile with ID \"{$this->getID()}\".");
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