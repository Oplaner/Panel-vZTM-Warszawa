<?php

final class DriverProfile extends Profile {
    private int $initialPenaltyMultiplier;
    private ?int $acquiredPenaltyMultiplier;

    private function __construct(?string $id, string $userID, SystemDateTime $activatedAt, User $activatedBy, ?SystemDateTime $deactivatedAt, ?User $deactivatedBy, int $initialPenaltyMultiplier, ?int $acquiredPenaltyMultiplier) {
        parent::__construct($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy);
        $this->initialPenaltyMultiplier = $initialPenaltyMultiplier;
        $this->acquiredPenaltyMultiplier = $acquiredPenaltyMultiplier;
        $this->save();
    }

    public static function createNew(User $owner, User $activator): ?DriverProfile {
        Logger::log(LogLevel::info, "User with ID \"{$activator->getID()}\" is creating new driver profile for user with ID \"{$owner->getID()}\".");
        self::validateUserDoesNotHaveProfileOfType($owner);
        $initialPenaltyMultiplier = self::getNextPenaltyMultiplierForUser($owner);
        return new DriverProfile(null, $owner->getID(), SystemDateTime::now(), $activator, null, null, $initialPenaltyMultiplier, null);
    }

    public static function withID(string $id): ?DriverProfile {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, DriverProfile::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT p.user_id, p.activated_at, p.activated_by_user_id, p.deactivated_at, p.deactivated_by_user_id, pd.initial_penalty_multiplier, pd.acquired_penalty_multiplier
            FROM profiles AS p
            INNER JOIN profiles_driver AS pd
            ON p.id = pd.profile_id
            WHERE p.id = ? AND p.type = ?",
            [
                $id,
                self::DATABASE_PROFILE_TYPE_DRIVER
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find driver profile with ID \"$id\".");
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
        $initialPenaltyMultiplier = $data["initial_penalty_multiplier"];
        $acquiredPenaltyMultiplier = $data["acquired_penalty_multiplier"];
        return new DriverProfile($id, $userID, $activatedAt, $activatedBy, $deactivatedAt, $deactivatedBy, $initialPenaltyMultiplier, $acquiredPenaltyMultiplier);
    }

    public static function getNextPenaltyMultiplierForUser(User $user): int {
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT pd.acquired_penalty_multiplier
            FROM profiles AS p
            INNER JOIN profiles_driver AS pd
            ON p.id = pd.profile_id
            WHERE p.user_id = ? AND p.type = ?
            ORDER BY p.activated_at DESC
            LIMIT 1",
            [
                $user->getID(),
                self::DATABASE_PROFILE_TYPE_DRIVER
            ]
        );

        if ($result->num_rows == 0) {
            $result->free();
            return 0;
        }

        $acquiredPenaltyMultiplier = $result->fetch_column();
        $result->free();
        return $acquiredPenaltyMultiplier ?? 0;
    }

    public function getInitialPenaltyMultiplier(): int {
        return $this->initialPenaltyMultiplier;
    }

    public function getAcquiredPenaltyMultiplier(): ?int {
        return $this->acquiredPenaltyMultiplier;
    }

    public function incrementPenaltyMultiplier(): void {
        if (!$this->isActive()) {
            return;
        }

        $this->acquiredPenaltyMultiplier = $this->initialPenaltyMultiplier + 1;
        $this->wasModified = true;
        $this->save();
    }

    public function deactivate(User $deactivator): void {
        if (is_null($this->acquiredPenaltyMultiplier)) {
            $this->acquiredPenaltyMultiplier = 0;
        }

        parent::deactivate($deactivator);
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", userID: \"%s\", activatedAt: %s, activatedByUserID: \"%s\", deactivatedAt: %s, deactivatedByUserID: %s, initialPenaltyMultiplier: %d, acquiredPenaltyMultiplier: %s)",
            $this->id,
            $this->userID,
            $this->activatedAt->toDatabaseString(),
            $this->activatedBy->getID(),
            is_null($this->deactivatedAt) ? "null" : $this->deactivatedAt->toDatabaseString(),
            is_null($this->deactivatedBy) ? "null" : "\"{$this->deactivatedBy->getID()}\"",
            $this->initialPenaltyMultiplier,
            $this->acquiredPenaltyMultiplier ?? "null"
        );
    }

    protected function save(): void {
        if ($this->isNew) {
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO profiles_driver
                (profile_id, initial_penalty_multiplier, acquired_penalty_multiplier)
                VALUES (?, ?, ?)",
                [
                    $this->id,
                    $this->initialPenaltyMultiplier,
                    $this->acquiredPenaltyMultiplier
                ]
            );
            $this->saveNewProfileToDatabase(self::DATABASE_PROFILE_TYPE_DRIVER);
            $this->isNew = false;
        } elseif ($this->wasModified) {
            DatabaseConnector::shared()->execute_query(
                "UPDATE profiles_driver
                SET acquired_penalty_multiplier = ?
                WHERE profile_id = ?",
                [
                    $this->acquiredPenaltyMultiplier,
                    $this->id
                ]
            );
            $this->saveExistingProfileToDatabase();
            $this->wasModified = false;
        }
    }
}

?>