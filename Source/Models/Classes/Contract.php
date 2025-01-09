<?php

final class Contract extends DatabaseEntity {
    private Carrier $carrier;
    private User $driver;
    private ContractState $currentState;
    private int $initialPenaltyTasks;
    private int $remainingPenaltyTasks;

    private function __construct(?string $id, Carrier $carrier, User $driver, ContractState $currentState, int $initialPenaltyTasks, int $remainingPenaltyTasks) {
        parent::__construct($id);
        $this->carrier = $carrier;
        $this->driver = $driver;
        $this->currentState = $currentState;
        $this->initialPenaltyTasks = $initialPenaltyTasks;
        $this->remainingPenaltyTasks = $remainingPenaltyTasks;
        $this->save();
    }

    public static function createNew(Carrier $carrier, User $driver, User $authorizer, ContractState $state): Contract {
        Logger::log(LogLevel::info, "User with ID \"{$authorizer->getID()}\" is creating new contract between carrier \"{$carrier->getShortName()}\" and user with ID \"{$driver->getID()}\", with initial state \"{$state->value}\".");
        self::validateContractStateIsNotFinal($state);
        $driverProfile = DriverProfile::createNew($driver, $authorizer);
        $totalPenaltyTasks = 0;

        if ($state == ContractState::probationWithPenalty) {
            $penaltyTasksMultiplier = $driverProfile->getInitialPenaltyMultiplier();
            $initialPenaltyTasks = $carrier->getNumberOfPenaltyTasks();
            $totalPenaltyTasks = $penaltyTasksMultiplier * $initialPenaltyTasks;
        }

        $contract = new Contract(null, $carrier, $driver, $state, $totalPenaltyTasks, $totalPenaltyTasks);
        ContractPeriod::createNew($contract, $state, SystemDateTime::now(), $authorizer);
        return $contract;
    }

    public static function withID(string $id): ?Contract {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, Contract::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT carrier_id, driver_id, current_state, initial_penalty_tasks, remaining_penalty_tasks
            FROM contracts
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find contract with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $carrier = Carrier::withID($data["carrier_id"]);
        $driver = User::withID($data["driver_id"]);
        $state = ContractState::from($data["current_state"]);
        $initialPenaltyTasks = $data["initial_penalty_tasks"];
        $remainingPenaltyTasks = $data["remaining_penalty_tasks"];
        return new Contract($id, $carrier, $driver, $state, $initialPenaltyTasks, $remainingPenaltyTasks);
    }

    public static function getAllByCarrier(Carrier $carrier): array {
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT c.id
            FROM contracts AS c
            INNER JOIN contract_periods AS cp
            ON c.id = cp.contract_id
            WHERE c.carrier_id = ?
            GROUP BY c.id
            ORDER BY MIN(cp.valid_from) ASC",
            [
                $carrier->getID()
            ]
        );
        $contracts = [];

        while ($data = $result->fetch_assoc()) {
            $contractID = $data["id"];
            $contracts[] = self::withID($contractID);
        }

        $result->free();
        return $contracts;
    }

    public static function getAllByDriver(User $driver): array {
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT c.id
            FROM contracts AS c
            INNER JOIN contract_periods AS cp
            ON c.id = cp.contract_id
            WHERE c.driver_id = ?
            GROUP BY c.id
            ORDER BY MIN(cp.valid_from) ASC",
            [
                $driver->getID()
            ]
        );
        $contracts = [];

        while ($data = $result->fetch_assoc()) {
            $contractID = $data["id"];
            $contracts[] = self::withID($contractID);
        }

        $result->free();
        return $contracts;
    }

    private static function validateContractStateIsNotFinal(ContractState $state): void {
        if ($state->isFinal()) {
            throw new Exception("Cannot create new contract with final state.");
        }
    }

    public function getCarrier(): Carrier {
        return $this->carrier;
    }

    public function getDriver(): User {
        return $this->driver;
    }

    public function getCurrentState(): ContractState {
        return $this->currentState;
    }

    public function setCurrentState(ContractState $state): void {
        $this->currentState = $state;
        $this->wasModified = true;
        $this->save();
    }

    public function getPeriods(): array {
        return ContractPeriod::getAllByContract($this);
    }

    public function addPeriod(ContractState $state, User $authorizer): void {
        if (!$this->isActive()) {
            throw new Exception("Cannot add new period to inactive contract.");
        }

        $periods = $this->getPeriods();
        $lastPeriod = $periods[count($periods) - 1];
        $lastPeriod->setValidTo(SystemDateTime::now());
        ContractPeriod::createNew($this, $state, $lastPeriod->getValidTo(), $authorizer);
        $this->setCurrentState($state);

        match ($state) {
            ContractState::terminated => $this->deactivateDriverProfileIfNeeded($authorizer),
            ContractState::terminatedDisciplinarily => $this->handleDisciplinarTermination($authorizer),
            default => null
        };
    }

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
        $this->save();
    }

    public function isActive(): bool {
        return !$this->currentState->isFinal();
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", carrierID: \"%s\", driverID: \"%s\", currentState: \"%s\", initialPenaltyTasks: %d, remainingPenaltyTasks: %d)",
            $this->id,
            $this->carrier->getID(),
            $this->driver->getID(),
            $this->currentState->value,
            $this->initialPenaltyTasks,
            $this->remainingPenaltyTasks
        );
    }

    protected function save(): void {
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $db->execute_query(
                "INSERT INTO contracts
                (id, carrier_id, driver_id, current_state, initial_penalty_tasks, remaining_penalty_tasks)
                VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->carrier->getID(),
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

    private function getDriverActiveContracts(): array {
        return array_filter(
            self::getAllByDriver($this->driver),
            fn ($contract) => $contract->isActive()
        );
    }

    private function getDriverProfile(): DriverProfile {
        return array_filter(
            $this->driver->getProfiles(),
            fn ($profile) => $profile->isActive() && is_a($profile, DriverProfile::class)
        )[0];
    }

    private function deactivateDriverProfileIfNeeded(User $authorizer): void {
        $driverActiveContracts = $this->getDriverActiveContracts();

        if (count($driverActiveContracts) == 0) {
            Logger::log(LogLevel::info, "The last contract of user with ID \"{$this->driver->getID()}\" has been terminated. Their driver profile deactivation will follow.");
            $this->getDriverProfile()->deactivate($authorizer);
        }
    }

    private function handleDisciplinarTermination(User $authorizer): void {
        Logger::log(LogLevel::info, "The contract of user with ID \"{$this->driver->getID()}\" has been terminated disciplinarily. Their other contracts termination will follow.");
        $this->terminateDriverActiveContracts($authorizer);
        $driverProfile = $this->getDriverProfile();
        $driverProfile->incrementPenaltyMultiplier();
        $driverProfile->deactivate($authorizer);
    }

    private function terminateDriverActiveContracts(User $authorizer): void {
        $driverActiveContracts = $this->getDriverActiveContracts();
        array_walk(
            $driverActiveContracts,
            fn ($contract) => $contract->addPeriod(ContractState::terminatedAutomatically, $authorizer)
        );
    }
}

?>