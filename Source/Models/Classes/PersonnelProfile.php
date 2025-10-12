<?php

final class PersonnelProfile extends Profile {
    private string $description;

    private function __construct(?string $id, string $userID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy, string $description) {
        parent::__construct($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy);
        $this->description = $description;
        $this->save();
    }

    public static function createNew(User $owner, User $activator, string $description, array $privileges): PersonnelProfile {
        Logger::log(LogLevel::info, "User with ID \"{$activator->getID()}\" is creating new personnel profile with ".count($privileges)." privilege(s) for user with ID \"{$owner->getID()}\".");
        self::validatePrivilegesArrayIsNotEmpty($privileges);
        self::validateUserDoesNotHaveProfileOfType($owner);
        $personnelProfile = new PersonnelProfile(null, $owner->getID(), SystemDateTime::now(), $activator, null, null, $description);
        $personnelProfile->setPrivileges($privileges);
        return $personnelProfile;
    }

    public static function withID(string $id): ?PersonnelProfile {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, PersonnelProfile::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT p.user_id, p.activated_at, p.activated_by_user_id, p.deactivated_at, p.deactivated_by_user_id, pp.description
            FROM profiles AS p
            INNER JOIN profiles_personnel AS pp
            ON p.id = pp.profile_id
            WHERE p.id = ? AND p.type = ?",
            [
                $id,
                self::DATABASE_PROFILE_TYPE_PERSONNEL
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find personnel profile with ID \"$id\".");
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
        $description = $data["description"];
        return new PersonnelProfile($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy, $description);
    }

    private static function validatePrivilegesArrayIsNotEmpty($privileges): void {
        if (count($privileges) == 0) {
            throw new DomainException("Creating personnel profile with 0 privileges is not allowed.");
        }
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPrivileges(): array {
        return Privilege::getAllByPersonnelProfile($this);
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
            count($this->getPrivileges())
        );
    }

    protected function save(): void {
        if ($this->isNew) {
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO profiles_personnel
                (profile_id, description)
                VALUES (?, ?)",
                [
                    $this->id,
                    $this->description
                ]
            );
            $this->saveNewProfileToDatabase(self::DATABASE_PROFILE_TYPE_PERSONNEL);
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $this->saveExistingProfileToDatabase();
            $this->wasModified = false;
        }
    }

    private function setPrivileges(array $privileges): void {
        foreach ($privileges as $privilege) {
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO personnel_profile_privileges
                (personnel_profile_id, privilege_id)
                VALUES (?, ?)",
                [
                    $this->id,
                    $privilege->getID()
                ]
            );
        }
    }
}

?>