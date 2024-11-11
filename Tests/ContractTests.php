<?php

final class ContractTests {
    private const EXISTING_TEST_USER_LOGIN = 1387;

    public static function throwExceptionWhenCreatingContractWithFinalState(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        self::deleteTestUser($user->getID());

        try {
            Contract::createNew($user, $user, ContractState::terminated, 0);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when creating a contract with final state.";
    }

    public static function throwExceptionWhenCreatingContractWithNegativeNumberOfInitialPenaltyTasks(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        self::deleteTestUser($user->getID());

        try {
            Contract::createNew($user, $user, ContractState::regular, -1);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when creating a contract with negative number of initial penalty tasks.";
    }

    public static function createNewContract(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $contractStates = [
            ContractState::conditional,
            ContractState::conditionalWithPenalty,
            ContractState::regular
        ];
        $contractState = $contractStates[array_rand($contractStates)];
        $initialPenaltyTasks = random_int(0, 5);
        $contract = Contract::createNew($user, $user, $contractState, $initialPenaltyTasks);
        $periods = $contract->getPeriods();

        self::deleteContractData($contract->getID());
        self::deleteTestUser($user->getID());

        if (!is_a($contract, Contract::class)) {
            return "Expected a ".Contract::class." object. Found: ".gettype($contract).".";
        } elseif ($contract->getCurrentState() != $contractState) {
            return "New contract was created with incorrect state. Expected: {$contractState->name}, found: {$contract->getCurrentState()->name}.";
        } elseif ($contract->getInitialPenaltyTasks() != $initialPenaltyTasks) {
            return "New contract initial penalty tasks value is incorrect. Expected: {$contract->getInitialPenaltyTasks()}, found: $initialPenaltyTasks.";
        } elseif ($contract->getRemainingPenaltyTasks() != $initialPenaltyTasks) {
            return "New contract remaining penalty tasks value should be equal to initial penalty tasks. Expected: $initialPenaltyTasks, found: {$contract->getRemainingPenaltyTasks()}.";
        } elseif (count($periods) != 1) {
            return "New contract has incorrect number of periods. Expected: 1, found: ".count($periods).".";
        } elseif ($periods[0]->getState() != $contractState) {
            return "The contract period has incorrect state. Expected: {$contractState->name}, found: {$periods[0]->getState()->name}.";
        } elseif ($periods[0]->getAuthorizedBy()->getID() != $user->getID()) {
            return "The contract period authorizer is incorrect. Expected user ID: \"{$user->getID()}\", found: \"{$periods[0]->getAuthorizedBy()->getID()}\".";
        } elseif (!is_null($periods[0]->getValidTo())) {
            return "The contract period validTo value should be null.";
        }

        return true;
    }

    public static function getContract(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $contractStates = [
            ContractState::conditional,
            ContractState::conditionalWithPenalty,
            ContractState::regular
        ];
        $contractState = $contractStates[array_rand($contractStates)];
        $initialPenaltyTasks = random_int(0, 5);
        $contract = Contract::createNew($user, $user, $contractState, $initialPenaltyTasks);
        $contractID = $contract->getID();
        $periods = $contract->getPeriods();
        DatabaseEntity::removeFromCache($contract);
        DatabaseEntity::removeFromCache($periods[0]);

        $contract = Contract::withID($contractID);
        $periods = $contract->getPeriods();

        self::deleteContractData($contractID);
        self::deleteTestUser($user->getID());

        if (!is_a($contract, Contract::class)) {
            return "Expected a ".Contract::class." object. Found: ".gettype($contract).".";
        } elseif ($contract->getCurrentState() != $contractState) {
            return "New contract was created with incorrect state. Expected: {$contractState->name}, found: {$contract->getCurrentState()->name}.";
        } elseif (count($periods) != 1) {
            return "New contract has incorrect number of periods. Expected: 1, found: ".count($periods).".";
        }

        return true;
    }

    public static function throwExceptionWhenAddingPeriodToContractInFinalState(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $contract = Contract::createNew($user, $user, ContractState::regular, 0);
        $contract->addPeriod(ContractState::terminatedDisciplinarily, $user);

        self::deleteContractData($contract->getID());
        self::deleteTestUser($user->getID());

        try {
            $contract->addPeriod(ContractState::regular, $user);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when adding new period to contract with final state.";
    }

    public static function addContractPeriod(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $contract = Contract::createNew($user, $user, ContractState::regular, 0);
        $newState = ContractState::terminated;
        $contract->addPeriod($newState, $user);

        $periods = $contract->getPeriods();

        self::deleteContractData($contract->getID());
        self::deleteTestUser($user->getID());

        if (count($periods) != 2) {
            return "The number of contract periods is incorrect. Expected: 2, found: ".count($periods).".";
        } elseif (is_null($periods[0]->getValidTo())) {
            return "The previous contract period validTo value should not be null.";
        } elseif (!$periods[1]->getValidFrom()->isEqual($periods[0]->getValidTo())) {
            return "The new contract period validFrom is not equal to previous period validTo value.";
        } elseif ($periods[1]->getState() != $newState) {
            return "The new contract period state is incorrect. Expected: {$newState->name}, found: {$periods[1]->getState()->name}.";
        } elseif ($contract->getCurrentState() != $newState) {
            return "Current contract state is incorrect. Expected: {$newState->name}, found: {$contract->getCurrentState()->name}.";
        }

        return true;
    }

    public static function decrementRemainingPenaltyTasksStartingWithPositiveValue(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $initialPenaltyTasks = random_int(1, 5);
        $contract = Contract::createNew($user, $user, ContractState::regular, $initialPenaltyTasks);

        $valueBeforeChange = $contract->getRemainingPenaltyTasks();
        $contract->decrementRemainingPenaltyTasks();
        $valueAfterChange = $contract->getRemainingPenaltyTasks();

        self::deleteContractData($contract->getID());
        self::deleteTestUser($user->getID());

        if ($valueAfterChange == $valueBeforeChange) {
            return "Contract remaining penalty tasks value did not change.";
        } elseif ($valueAfterChange != $valueBeforeChange - 1) {
            return "Contract remaining penalty tasks value after change is incorrect. Expected: ".($valueBeforeChange - 1).", found: $valueAfterChange.";
        }

        return true;
    }

    public static function decrementRemainingPenaltyTasksStartingWithZero(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $contract = Contract::createNew($user, $user, ContractState::regular, 0);

        $valueBeforeChange = $contract->getRemainingPenaltyTasks();
        $contract->decrementRemainingPenaltyTasks();
        $valueAfterChange = $contract->getRemainingPenaltyTasks();

        self::deleteContractData($contract->getID());
        self::deleteTestUser($user->getID());

        if ($valueAfterChange != $valueBeforeChange) {
            return "Contract remaining penalty tasks value should not have changed.";
        } elseif ($valueAfterChange != 0) {
            return "Contract remaining penalty tasks value after change is incorrect. Expected: 0, found: $valueAfterChange.";
        }

        return true;
    }

    private static function deleteContractData(string $contractID): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM contract_periods
            WHERE contract_id = ?",
            [
                $contractID
            ]
        );
        $db->execute_query(
            "DELETE FROM contracts
            WHERE id = ?",
            [
                $contractID
            ]
        );
    }

    private static function deleteTestUser(string $userID): void {
        DatabaseConnector::shared()->execute_query(
            "DELETE FROM users
            WHERE id = ?",
            [
                $userID
            ]
        );
    }
}

?>