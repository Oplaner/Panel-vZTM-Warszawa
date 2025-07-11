<?php

final class Carrier extends DatabaseEntity {
    private string $fullName;
    private string $shortName;
    private int $numberOfTrialTasks;
    private int $numberOfPenaltyTasks;
    private SystemDateTime $createdAt;
    private User $createdBy;
    private ?SystemDateTime $closedAt;
    private ?User $closedBy;

    protected function __construct(?string $id, string $fullName, string $shortName, int $numberOfTrialTasks, int $numberOfPenaltyTasks, SystemDateTime $createdAt, User $createdBy, ?SystemDateTime $closedAt, ?User $closedBy) {
        parent::__construct($id);
        $this->fullName = $fullName;
        $this->shortName = $shortName;
        $this->numberOfTrialTasks = $numberOfTrialTasks;
        $this->numberOfPenaltyTasks = $numberOfPenaltyTasks;
        $this->createdAt = $createdAt;
        $this->createdBy = $createdBy;
        $this->closedAt = $closedAt;
        $this->closedBy = $closedBy;
        $this->save();
    }

    public static function createNew(string $fullName, string $shortName, array $supervisors, int $numberOfTrialTasks, int $numberOfPenaltyTasks, User $creator): Carrier {
        Logger::log(LogLevel::info, "User with ID \"{$creator->getID()}\" is creating new carrier with name \"$fullName\" and ".count($supervisors)." supervisor(s).");
        self::validateNumberOfTrialTasksIsNotLessThanZero($numberOfTrialTasks);
        self::validateNumberOfPenaltyTasksIsNotLessThanZero($numberOfPenaltyTasks);
        $carrier = new Carrier(null, $fullName, $shortName, $numberOfTrialTasks, $numberOfPenaltyTasks, SystemDateTime::now(), $creator, null, null);
        array_walk($supervisors, fn($supervisor) => $carrier->addSupervisor($supervisor));
        return $carrier;
    }

    public static function withID(string $id): ?Carrier {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, Carrier::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT full_name, short_name, trial_tasks, penalty_tasks, created_at, created_by_user_id, closed_at, closed_by_user_id
            FROM carriers
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find carrier with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $fullName = $data["full_name"];
        $shortName = $data["short_name"];
        $numberOfTrialTasks = $data["trial_tasks"];
        $numberOfPenaltyTasks = $data["penalty_tasks"];
        $createdAt = new SystemDateTime($data["created_at"]);
        $createdBy = User::withID($data["created_by_user_id"]);
        $closedAt = is_null($data["closed_at"]) ? null : new SystemDateTime($data["closed_at"]);
        $closedBy = is_null($data["closed_by_user_id"]) ? null : User::withID($data["closed_by_user_id"]);
        return new Carrier($id, $fullName, $shortName, $numberOfTrialTasks, $numberOfPenaltyTasks, $createdAt, $createdBy, $closedAt, $closedBy);
    }

    public static function getAll(): array {
        $query =
        "SELECT c.id
        FROM carriers AS c
        ORDER BY c.created_at ASC";
        return self::getWithQuery($query);
    }
    
    public static function getAllActive(): array {
        $query =
        "SELECT c.id
        FROM carriers AS c
        WHERE closed_at IS NULL
        ORDER BY c.created_at ASC";
        return self::getWithQuery($query);
    }

    private static function getWithQuery(string $query, ?array $parameters = null): array {
        $result = DatabaseConnector::shared()->execute_query($query, $parameters);
        $carriers = [];

        while ($data = $result->fetch_assoc()) {
            $carrierID = $data["id"];
            $carriers[] = self::withID($carrierID);
        }

        $result->free();
        return $carriers;
    }

    private static function validateNumberOfTrialTasksIsNotLessThanZero(int $numberOfTrialTasks): void {
        if ($numberOfTrialTasks < 0) {
            throw new Exception("Number of trial tasks cannot be less than 0.");
        }
    }

    private static function validateNumberOfPenaltyTasksIsNotLessThanZero(int $numberOfPenaltyTasks): void {
        if ($numberOfPenaltyTasks < 0) {
            throw new Exception("Number of penalty tasks cannot be less than 0.");
        }
    }

    public function getFullName(): string {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void {
        $this->fullName = $fullName;
        $this->wasModified = true;
        $this->save();
    }

    public function getShortName(): string {
        return $this->shortName;
    }

    public function setShortName(string $shortName): void {
        $this->shortName = $shortName;
        $this->wasModified = true;
        $this->save();
    }

    public function getSupervisors(): array {
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT supervisor_id
            FROM carrier_supervisors
            WHERE carrier_id = ?",
            [
                $this->id
            ]
        );
        $supervisors = [];

        while ($supervisorID = $result->fetch_column()) {
            $supervisors[] = User::withID($supervisorID);
        }

        $result->free();
        return $supervisors;
    }

    public function addSupervisor(User $supervisor): void {
        $supervisors = $this->getSupervisors();

        if (!in_array($supervisor, $supervisors)) {
            Logger::log(LogLevel::info, "Adding user with ID \"{$supervisor->getID()}\" as supervisor of carrier with ID \"$this->id\".");
            DatabaseConnector::shared()->execute_query(
                "INSERT INTO carrier_supervisors
                (carrier_id, supervisor_id)
                VALUES (?, ?)",
                [
                    $this->id,
                    $supervisor->getID()
                ]
            );
        }
    }

    public function removeSupervisor(User $supervisor): void {
        $supervisors = $this->getSupervisors();

        if (in_array($supervisor, $supervisors)) {
            Logger::log(LogLevel::info, "Removing user with ID \"{$supervisor->getID()}\" from supervisors of carrier with ID \"$this->id\".");
            DatabaseConnector::shared()->execute_query(
                "DELETE FROM carrier_supervisors
                WHERE carrier_id = ? AND supervisor_id = ?",
                [
                    $this->id,
                    $supervisor->getID()
                ]
            );
        }
    }

    public function getAllContracts(): array {
        return Contract::getAllByCarrier($this);
    }

    public function getActiveContracts(): array {
        return Contract::getActiveByCarrier($this);
    }

    public function getNumberOfTrialTasks(): int {
        return $this->numberOfTrialTasks;
    }

    public function setNumberOfTrialTasks(int $numberOfTrialTasks): void {
        self::validateNumberOfTrialTasksIsNotLessThanZero($numberOfTrialTasks);
        $this->numberOfTrialTasks = $numberOfTrialTasks;
        $this->wasModified = true;
        $this->save();
    }

    public function getNumberOfPenaltyTasks(): int {
        return $this->numberOfPenaltyTasks;
    }

    public function setNumberOfPenaltyTasks(int $numberOfPenaltyTasks): void {
        self::validateNumberOfPenaltyTasksIsNotLessThanZero($numberOfPenaltyTasks);
        $this->numberOfPenaltyTasks = $numberOfPenaltyTasks;
        $this->wasModified = true;
        $this->save();
    }

    public function getCreatedAt(): SystemDateTime {
        return $this->createdAt;
    }

    public function getCreatedBy(): User {
        return $this->createdBy;
    }

    public function getClosedAt(): ?SystemDateTime {
        return $this->closedAt;
    }

    public function getClosedBy(): ?User {
        return $this->closedBy;
    }

    public function isActive(): bool {
        return is_null($this->closedAt);
    }

    public function close(User $authorizer): void {
        Logger::log(LogLevel::info, "User with ID \"{$authorizer->getID()}\" is closing carrier with ID \"$this->id\".");
        $this->closedAt = SystemDateTime::now();
        $this->closedBy = $authorizer;
        $this->wasModified = true;
        $this->save();
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", fullName: \"%s\", shortName: \"%s\", numberOfTrialTasks: %d, numberOfPenaltyTasks: %d, createdAt: %s, createdByUserID: \"%s\", closedAt: %s, closedByUserID: %s)",
            $this->id,
            $this->fullName,
            $this->shortName,
            $this->numberOfTrialTasks,
            $this->numberOfPenaltyTasks,
            $this->createdAt->toDatabaseString(),
            $this->createdBy->getID(),
            is_null($this->closedAt) ? "null" : $this->closedAt->toDatabaseString(),
            is_null($this->closedBy) ? "null" : "\"{$this->closedBy->getID()}\""
        );
    }

    protected function save(): void {
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $db->execute_query(
                "INSERT INTO carriers
                (id, full_name, short_name, trial_tasks, penalty_tasks, created_at, created_by_user_id, closed_at, closed_by_user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->fullName,
                    $this->shortName,
                    $this->numberOfTrialTasks,
                    $this->numberOfPenaltyTasks,
                    $this->createdAt->toDatabaseString(),
                    $this->createdBy->getID(),
                    null,
                    null
                ]
            );
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE carriers
                SET full_name = ?, short_name = ?, trial_tasks = ?, penalty_tasks = ?, closed_at = ?, closed_by_user_id = ?
                WHERE id = ?",
                [
                    $this->fullName,
                    $this->shortName,
                    $this->numberOfTrialTasks,
                    $this->numberOfPenaltyTasks,
                    $this->closedAt?->toDatabaseString() ?? null,
                    $this->closedBy?->getID() ?? null,
                    $this->id
                ]
            );
            $this->wasModified = false;
        }
    }
}

?>