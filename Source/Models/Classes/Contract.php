<?php

final class Contract extends DatabaseEntity {
    private User $driver;
    private ContractState $currentState;
    private ?array $periods = null;
    private int $initialPenaltyTasks;
    private int $remainingPenaltyTasks;

    private function __construct(?string $id, User $driver, ContractState $currentState, int $initialPenaltyTasks, int $remainingPenaltyTasks) {
        parent::__construct($id);
        $this->driver = $driver;
        $this->currentState = $currentState;
        $this->initialPenaltyTasks = $initialPenaltyTasks;
        $this->remainingPenaltyTasks = $remainingPenaltyTasks;
        $this->save();
    }

    public static function createNew(User $driver, User $authorizer, ContractState $state, int $initialPenaltyTasks): Contract {
        Logger::log(LogLevel::info, "User with ID \"{$authorizer->getID()}\" is creating new contract between TODO and driver with ID \"{$driver->getID()}\", with initial state \"{$state->value}\" and $initialPenaltyTasks initial penalty task(s).");
        self::validateContractStateIsNotFinal($state);
        self::validateNumberOfInitialPenaltyTasksIsNotLessThanZero($initialPenaltyTasks);
        $contract = new Contract(null, $driver, $state, $initialPenaltyTasks, $initialPenaltyTasks);
        ContractPeriod::createNew($contract, $state, SystemDateTime::now(), $authorizer);
        return $contract;
    }

    public static function withID(string $id): ?Contract {
        Logger::log(LogLevel::info, "Fetching contract with ID \"$id\".");
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, Contract::class)) {
            Logger::log(LogLevel::info, "Found cached contract: $cachedObject.");
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT driver_id, current_state, initial_penalty_tasks, remaining_penalty_tasks
            FROM contracts
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find existing contract with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $driver = User::withID($data["driver_id"]);
        $state = ContractState::from($data["current_state"]);
        $initialPenaltyTasks = $data["initial_penalty_tasks"];
        $remainingPenaltyTasks = $data["remaining_penalty_tasks"];
        $contract = new Contract($id, $driver, $state, $initialPenaltyTasks, $remainingPenaltyTasks);
        Logger::log(LogLevel::info, "Fetched existing contract: $contract.");
        return $contract;
    }

    private static function validateContractStateIsNotFinal(ContractState $state): void {
        if ($state->isFinal()) {
            throw new Exception("Cannot create new contract with final state.");
        }
    }

    private static function validateNumberOfInitialPenaltyTasksIsNotLessThanZero(int $initialPenaltyTasks): void {
        if ($initialPenaltyTasks < 0) {
            throw new Exception("Number of initial penalty tasks cannot be less than 0.");
        }
    }

    public function getDriver(): User {
        return $this->driver;
    }

    public function getCurrentState(): ContractState {
        return $this->currentState;
    }

    public function getPeriods(): array {
        if (is_null($this->periods)) {
            $this->periods = ContractPeriod::getAllPeriodsOfContract($this);
        }

        return $this->periods;
    }

    // TODO: Handle updating periods and currentState after adding new period.

    public function getInitialPenaltyTasks(): int {
        return $this->initialPenaltyTasks;
    }

    public function getRemainingPenaltyTasks(): int {
        return $this->remainingPenaltyTasks;
    }

    public function decrementRemainingPenaltyTasks(): void {
        $valueBeforeChange = $this->remainingPenaltyTasks;
        $this->remainingPenaltyTasks = max(0, $this->remainingPenaltyTasks - 1);
        $this->wasModified = $valueBeforeChange != $this->remainingPenaltyTasks;
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", driverID: \"%s\", currentState: \"%s\", initialPenaltyTasks: %d, remainingPenaltyTasks: %d)",
            $this->id,
            $this->driver->getID(),
            $this->currentState->value,
            $this->initialPenaltyTasks,
            $this->remainingPenaltyTasks
        );
    }

    protected function save(): void {
        Logger::log(LogLevel::info, "Saving ".($this->isNew ? "new" : "existing")." personnel profile: $this.");
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $db->execute_query(
                "INSERT INTO contracts
                (id, driver_id, current_state, initial_penalty_tasks, remaining_penalty_tasks)
                VALUES (?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->driver->getID(),
                    $this->currentState->value,
                    $this->initialPenaltyTasks,
                    $this->remainingPenaltyTasks
                ]
            );
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE contracts
                SET current_state = ?, remaining_penalty_tasks = ?
                WHERE id = ?",
                [
                    $this->currentState->value,
                    $this->remainingPenaltyTasks,
                    $this->id
                ]
            );
            $this->wasModified = false;
        }
    }
}

?>