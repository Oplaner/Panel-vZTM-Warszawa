<?php

final class ContractTests {
    public static function throwExceptionWhenCreatingContractWithFinalState(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($user);

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        try {
            Contract::createNew($carrier, $user, $user, ContractState::terminated);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when creating a contract with final state.";
    }

    public static function createNewContractWithoutPenalty(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($user);
        $contractState = ContractState::active;
        $contract = Contract::createNew($carrier, $user, $user, $contractState);
        $periods = $contract->getPeriods();

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($contract, Contract::class)) {
            return "Expected a ".Contract::class." object. Found: ".gettype($contract).".";
        } elseif ($contract->getCarrier()->getID() != $carrier->getID()) {
            return "New contract carrier ID is incorrect. Expected: \"{$carrier->getID()}\", found: \"{$contract->getCarrier()->getID()}\".";
        } elseif ($contract->getDriver()->getID() != $user->getID()) {
            return "New contract driver ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$contract->getDriver()->getID()}\".";
        } elseif ($contract->getCurrentState() != $contractState) {
            return "New contract was created with incorrect state. Expected: {$contractState->name}, found: {$contract->getCurrentState()->name}.";
        } elseif ($contract->getInitialPenaltyTasks() != 0) {
            return "New contract initialPenaltyTasks value is incorrect. Expected: 0, found: {$contract->getInitialPenaltyTasks()}.";
        } elseif ($contract->getRemainingPenaltyTasks() != 0) {
            return "New contract remainingPenaltyTasks value is incorrect. Expected: 0, found: {$contract->getRemainingPenaltyTasks()}.";
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

    public static function createNewContractWithPenalty(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = TestHelpers::createTestInactiveDriverProfileWithAcquiredPenalty($user);
        $carrier = TestHelpers::createTestCarrier($user);
        $contractState = ContractState::probationWithPenalty;
        $contract = Contract::createNew($carrier, $user, $user, $contractState);
        $periods = $contract->getPeriods();
        $expectedPenaltyTasks = $profile->getAcquiredPenaltyMultiplier() * $carrier->getNumberOfPenaltyTasks();

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($contract, Contract::class)) {
            return "Expected a ".Contract::class." object. Found: ".gettype($contract).".";
        } elseif ($contract->getCarrier()->getID() != $carrier->getID()) {
            return "New contract carrier ID is incorrect. Expected: \"{$carrier->getID()}\", found: \"{$contract->getCarrier()->getID()}\".";
        } elseif ($contract->getDriver()->getID() != $user->getID()) {
            return "New contract driver ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$contract->getDriver()->getID()}\".";
        } elseif ($contract->getCurrentState() != $contractState) {
            return "New contract was created with incorrect state. Expected: {$contractState->name}, found: {$contract->getCurrentState()->name}.";
        } elseif ($contract->getInitialPenaltyTasks() != $expectedPenaltyTasks) {
            return "New contract initialPenaltyTasks value is incorrect. Expected: $expectedPenaltyTasks, found: {$contract->getInitialPenaltyTasks()}.";
        } elseif ($contract->getRemainingPenaltyTasks() != $expectedPenaltyTasks) {
            return "New contract remainingPenaltyTasks value is incorrect. Expected: $expectedPenaltyTasks, found: {$contract->getRemainingPenaltyTasks()}.";
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
        $user = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($user);
        $contractStates = [
            ContractState::probation,
            ContractState::active
        ];
        $contractState = $contractStates[array_rand($contractStates)];
        $contract = Contract::createNew($carrier, $user, $user, $contractState);
        DatabaseEntity::removeFromCache($contract);
        $contract = Contract::withID($contract->getID());
        $periods = $contract->getPeriods();

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($contract, Contract::class)) {
            return "Expected a ".Contract::class." object. Found: ".gettype($contract).".";
        } elseif ($contract->getCarrier()->getID() != $carrier->getID()) {
            return "The contract carrier ID is incorrect. Expected: \"{$carrier->getID()}\", found: \"{$contract->getCarrier()->getID()}\".";
        } elseif ($contract->getDriver()->getID() != $user->getID()) {
            return "The contract driver ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$contract->getDriver()->getID()}\".";
        } elseif ($contract->getCurrentState() != $contractState) {
            return "The contract has incorrect state. Expected: {$contractState->name}, found: {$contract->getCurrentState()->name}.";
        } elseif (count($periods) != 1) {
            return "The contract has incorrect number of periods. Expected: 1, found: ".count($periods).".";
        }

        return true;
    }

    public static function throwExceptionWhenAddingPeriodToContractInFinalState(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($user);
        $contract = Contract::createNew($carrier, $user, $user, ContractState::active);
        $contract->addPeriod(ContractState::terminatedDisciplinarily, $user);

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        try {
            $contract->addPeriod(ContractState::active, $user);
        } catch (Exception $exception) {
            return true;
        }

        return "No exception was thrown when adding new period to contract with final state.";
    }

    public static function addContractPeriod(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($user);
        $contract = Contract::createNew($carrier, $user, $user, ContractState::active);
        $newState = ContractState::terminated;
        $contract->addPeriod($newState, $user);
        $periods = $contract->getPeriods();

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

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
        $user = TestHelpers::createTestUser();
        TestHelpers::createTestInactiveDriverProfileWithAcquiredPenalty($user);
        $carrier = TestHelpers::createTestCarrier($user);
        $contract = Contract::createNew($carrier, $user, $user, ContractState::probationWithPenalty);
        $valueBeforeChange = $contract->getRemainingPenaltyTasks();
        $contract->decrementRemainingPenaltyTasks();
        $valueAfterChange = $contract->getRemainingPenaltyTasks();

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueAfterChange == $valueBeforeChange) {
            return "Contract remainingPenaltyTasks value did not change.";
        } elseif ($valueAfterChange != $valueBeforeChange - 1) {
            return "Contract remainingPenaltyTasks value after change is incorrect. Expected: ".($valueBeforeChange - 1).", found: $valueAfterChange.";
        }

        return true;
    }

    public static function decrementRemainingPenaltyTasksStartingWithZero(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($user);
        $contract = Contract::createNew($carrier, $user, $user, ContractState::active);
        $valueBeforeChange = $contract->getRemainingPenaltyTasks();
        $contract->decrementRemainingPenaltyTasks();
        $valueAfterChange = $contract->getRemainingPenaltyTasks();

        TestHelpers::deleteAllTestDriverProfiles();
        TestHelpers::deleteTestContractData($contract->getID());
        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueAfterChange != $valueBeforeChange) {
            return "Contract remainingPenaltyTasks value should not have changed.";
        } elseif ($valueAfterChange != 0) {
            return "Contract remainingPenaltyTasks value after change is incorrect. Expected: 0, found: $valueAfterChange.";
        }

        return true;
    }
}

?>