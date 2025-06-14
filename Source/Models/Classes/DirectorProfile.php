<?php

final class DirectorProfile extends Profile {    
    private bool $isProtected;

    private function __construct(?string $id, string $userID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy, bool $isProtected) {
        parent::__construct($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy);
        $this->isProtected = $isProtected;
        $this->save();
    }

    public static function createNew(User $owner, User $activator): DirectorProfile {
        Logger::log(LogLevel::info, "User with ID \"{$activator->getID()}\" is creating new director profile for user with ID \"{$owner->getID()}\".");
        self::validateUserDoesNotHaveProfileOfType($owner);
        return new DirectorProfile(null, $owner->getID(), SystemDateTime::now(), $activator, null, null, false);
    }

    public static function withID(string $id): ?DirectorProfile {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, DirectorProfile::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT p.user_id, p.activated_at, p.activated_by_user_id, p.deactivated_at, p.deactivated_by_user_id, pd.protected
            FROM profiles AS p
            INNER JOIN profiles_director AS pd
            ON p.id = pd.profile_id
            WHERE p.id = ? AND p.type = ?",
            [
                $id,
                self::DATABASE_PROFILE_TYPE_DIRECTOR
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find director profile with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $userID = $data["user_id"];
        $activatedAt = new SystemDateTime($data["activated_at"]);
        $activatedBy = User::withID($data["activated_by_user_id"]);
        $deactivatedAt = is_null($data["deactivated_at"]) ? null : new SystemDateTime($data["deactivated_at"]);
        $deactivatedBy = is_null($data["deactivated_by_user_id"]) ? null : User::withID($data["deactivated_by_user_id"]);
        $isProtected = $data["protected"];
        return new DirectorProfile($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy, $isProtected);
    }

    public function isProtected(): bool {
        return $this->isProtected;
    }

    public function deactivate(User $deactivator): void {
        if ($this->isProtected) {
            Logger::log(LogLevel::warn, "User with ID \"{$deactivator->getID()}\" tried to deactivate protected director profile with ID \"{$this->id}\".");
            return;
        }

        parent::deactivate($deactivator);
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", userID: \"%s\", activatedAt: %s, activatedByUserID: \"%s\", deactivatedAt: %s, deactivatedByUserID: %s, isProtected: %s)",
            $this->id,
            $this->userID,
            $this->activatedAt->toDatabaseString(),
            $this->activatedBy->getID(),
            is_null($this->deactivatedAt) ? "null" : $this->deactivatedAt->toDatabaseString(),
            is_null($this->deactivatedBy) ? "null" : "\"{$this->deactivatedBy->getID()}\"",
            $this->isProtected ? "true" : "false"
        );
    }

    protected function save(): void {
        if ($this->isNew) {
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO profiles_director
                (profile_id, protected)
                VALUES (?, ?)",
                [
                    $this->id,
                    (int) $this->isProtected
                ]
            );
            $this->saveNewProfileToDatabase(self::DATABASE_PROFILE_TYPE_DIRECTOR);
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $this->saveExistingProfileToDatabase();
            $this->wasModified = false;
        }
    }
}

?>