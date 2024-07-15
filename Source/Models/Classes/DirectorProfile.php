<?php

final class DirectorProfile extends Profile {
    private const DATABASE_PROFILE_TYPE = "DIRECTOR";

    private bool $isProtected;

    private function __construct(?string $id, string $userID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy) {
        $this->setID($id);
        $this->userID = $userID;
        $this->activatedAt = $activatedAt;
        $this->activatedBy = $activatedBy;
        $this->deactivatedAt = $deactivatedAt;
        $this->deactivatedBy = $deactivatedBy;
        $this->isProtected = false;
    }

    public static function createNew(User $owner, User $activator): DirectorProfile {
        Logger::log(LogLevel::info, "User with ID \"{$activator->getID()}\" is creating new director profile for user with ID \"{$owner->getID()}\".");
        return new DirectorProfile(null, $owner->getID(), SystemDateTime::now(), $activator, null, null);
    }

    public static function withID(string $id): ?DirectorProfile {
        Logger::log(LogLevel::info, "Fetching director profile with ID \"$id\".");
        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT p.user_id, p.activated_at, p.activated_by_user_id, p.deactivated_at, p.deactivated_by_user_id, pd.protected
            FROM profiles AS p
            INNER JOIN profiles_director AS pd
            ON p.id = pd.profile_id
            WHERE p.id = ? AND p.type = \"".self::DATABASE_PROFILE_TYPE."\"",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find director profile with ID \"$id\".");
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
        $directorProfile = new DirectorProfile($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy, $isProtected);
        Logger::log(LogLevel::info, "Fetched director profile: $directorProfile.");
        return $directorProfile;
    }

    public function isProtected(): bool {
        return $this->isProtected;
    }

    public function save(): void {
        Logger::log(LogLevel::info, "Saving ".($this->isNew ? "new" : "existing")." director profile: $this.");
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $db->execute_query(
                "INSERT INTO profiles_director
                (profile_id, protected)
                VALUES (?, ?)",
                [
                    $this->id,
                    $this->isProtected
                ]
            );
            $db->execute_query(
                "INSERT INTO profiles
                (id, user_id, type, activated_at, activated_by_user_id, deactivated_at, deactivated_by_user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->userID,
                    self::DATABASE_PROFILE_TYPE,
                    $this->activatedAt->toDatabaseString(),
                    $this->activatedBy->getID(),
                    null,
                    null
                ]
            );
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE profiles
                SET deactivated_at = ?, deactivated_by_user_id = ?
                WHERE id = ?",
                [
                    $this->deactivatedAt->toDatabaseString(),
                    $this->deactivatedBy->getID()
                ]
            );
        }
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
}

?>