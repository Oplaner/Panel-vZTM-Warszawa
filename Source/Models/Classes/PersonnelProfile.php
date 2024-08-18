<?php

final class PersonnelProfile extends Profile {
    private string $description;
    private array $privileges;

    private function __construct(?string $id, string $userID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy, string $description, array $privileges) {
        parent::__construct($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy);
        $this->description = $description;
        $this->privileges = $privileges;
    }

    public static function createNew(User $owner, User $activator, string $description, array $privileges): PersonnelProfile {
        Logger::log(LogLevel::info, "User with ID \"{$activator->getID()}\" is creating new personnel profile with ".count($privileges)." privilege(s) for user with ID \"{$owner->getID()}\".");

        if (count($privileges) == 0) {
            throw new Exception("Creating a personnel profile with 0 privileges is not allowed.");
        }

        return new PersonnelProfile(null, $owner->getID(), SystemDateTime::now(), $activator, null, null, $description, $privileges);
    }

    public static function withID(string $id): ?PersonnelProfile {
        Logger::log(LogLevel::info, "Fetching personnel profile with ID \"$id\".");
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, PersonnelProfile::class)) {
            Logger::log(LogLevel::info, "Found cached personnel profile: $cachedObject.");
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT p.user_id, p.activated_at, p.activated_by_user_id, p.deactivated_at, p.deactivated_by_user_id, pp.description, ppp.privilege_id
            FROM profiles AS p
            INNER JOIN profiles_personnel AS pp
            ON p.id = pp.profile_id
            INNER JOIN personnel_profile_privileges AS ppp
            ON pp.profile_id = ppp.personnel_profile_id
            WHERE p.id = ? AND p.type = \"".self::DATABASE_PROFILE_TYPE_PERSONNEL."\"",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find personnel profile with ID \"$id\".");
            $result->free();
            return null;
        }

        $userID = null;
        $activatedAt = null;
        $activatedBy = null;
        $deactivatedAt = null;
        $deactivatedBy = null;
        $description = null;
        $privileges = [];

        while ($data = $result->fetch_assoc()) {
            if (is_null($userID)) {
                $userID = $data["user_id"];
                $activatedAt = new SystemDateTime($data["activated_at"]);
                $activatedBy = User::withID($data["activated_by_user_id"]);
                $deactivatedAt = is_null($data["deactivated_at"]) ? null : new SystemDateTime($data["deactivated_at"]);
                $deactivatedBy = is_null($data["deactivated_by_user_id"]) ? null : User::withID($data["deactivated_by_user_id"]);
                $description = $data["description"];
            }

            $privileges[] = Privilege::withID($data["privilege_id"]);
        }
        
        $result->free();
        $personnelProfile = new PersonnelProfile($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy, $description, $privileges);
        Logger::log(LogLevel::info, "Fetched personnel profile: $personnelProfile.");
        return $personnelProfile;
    }

    public static function historyForUser(User $user): array {
        Logger::log(LogLevel::info, "Fetching personnel profile history for user with ID \"{$user->getID()}\".");
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT id
            FROM profiles
            WHERE user_id = ? AND type = ?
            ORDER BY activated_at ASC",
            [
                $user->getID(),
                self::DATABASE_PROFILE_TYPE_PERSONNEL
            ]
        );

        $personnelProfiles = [];

        while ($data = $result->fetch_assoc()) {
            $profileID = $data["id"];
            $personnelProfiles[] = self::withID($profileID);
        }

        $result->free();
        Logger::log(LogLevel::info, "Found ".count($personnelProfiles)." personnel profile(s) in history for user with ID \"{$user->getID()}\".");
        return $personnelProfiles;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPrivileges(): array {
        return $this->privileges;
    }

    public function save(): void {
        Logger::log(LogLevel::info, "Saving ".($this->isNew ? "new" : "existing")." personnel profile: $this.");
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            foreach ($this->privileges as $privilege) {
                $db->execute_query(
                    "INSERT INTO personnel_profile_privileges
                    (personnel_profile_id, privilege_id)
                    VALUES (?, ?)",
                    [
                        $this->id,
                        $privilege->getID()
                    ]
                );
            }

            $db->execute_query(
                "INSERT INTO profiles_personnel
                (profile_id, description)
                VALUES (?, ?)",
                [
                    $this->id,
                    $this->description
                ]
            );
            $this->saveNewProfileToDatabase(self::DATABASE_PROFILE_TYPE_PERSONNEL);
        } elseif ($this->wasModified) {
            $this->saveExistingProfileToDatabase();
        }
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", userID: \"%s\", activatedAt: %s, activatedByUserID: \"%s\", deactivatedAt: %s, deactivatedByUserID: %s, description: \"%s\", privileges: (%d))",
            $this->id,
            $this->userID,
            $this->activatedAt->toDatabaseString(),
            $this->activatedBy->getID(),
            is_null($this->deactivatedAt) ? "null" : $this->deactivatedAt->toDatabaseString(),
            is_null($this->deactivatedBy) ? "null" : "\"{$this->deactivatedBy->getID()}\"",
            $this->description,
            count($this->privileges)
        );
    }
}

?>