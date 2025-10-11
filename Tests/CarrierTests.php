<?php

final class CarrierTests {
    public static function throwExceptionWhenCreatingCarrierWithNegativeNumberOfTrialTasks(): bool|string {
        $user = TestHelpers::createTestUser();

        TestHelpers::deleteTestUser($user->getID());

        try {
            Carrier::createNew("Test Carrier", "Test", [], -1, 0, $user);
        } catch (InvalidArgumentException) {
            return true;
        }

        return "No exception was thrown when creating a carrier with negative number of trial tasks.";
    }

    public static function throwExceptionWhenCreatingCarrierWithNegativeNumberOfPenaltyTasks(): bool|string {
        $user = TestHelpers::createTestUser();

        TestHelpers::deleteTestUser($user->getID());

        try {
            Carrier::createNew("Test Carrier", "Test", [], 0, -1, $user);
        } catch (InvalidArgumentException) {
            return true;
        }

        return "No exception was thrown when creating a carrier with negative number of penalty tasks.";
    }

    public static function createNewCarrierWithoutSupervisors(): bool|string {
        $user = TestHelpers::createTestUser();
        $fullName = "Test Carrier";
        $shortName = "Test";
        $numberOfTrialTasks = 3;
        $numberOfPenaltyTasks = 5;
        $carrier = Carrier::createNew($fullName, $shortName, [], $numberOfTrialTasks, $numberOfPenaltyTasks, $user);
        $supervisors = $carrier->getSupervisors();

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($carrier, Carrier::class)) {
            return "Expected a ".Carrier::class." object. Found: ".gettype($carrier).".";
        } elseif ($carrier->getFullName() != $fullName) {
            return "New carrier full name is incorrect. Expected: \"$fullName\", found: \"{$carrier->getFullName()}\".";
        } elseif ($carrier->getShortName() != $shortName) {
            return "New carrier short name is incorrect. Expected: \"$shortName\", found: \"{$carrier->getShortName()}\".";
        } elseif (count($supervisors) != 0) {
            return "New carrier has incorrect number of supervisors. Expected: 0, found: ".count($supervisors).".";
        } elseif ($carrier->getNumberOfTrialTasks() != $numberOfTrialTasks) {
            return "New carrier numberOfTrialTasks value is incorrect. Expected: $numberOfTrialTasks, found: {$carrier->getNumberOfTrialTasks()}.";
        } elseif ($carrier->getNumberOfPenaltyTasks() != $numberOfPenaltyTasks) {
            return "New carrier numberOfPenaltyTasks value is incorrect. Expected: $numberOfPenaltyTasks, found: {$carrier->getNumberOfPenaltyTasks()}.";
        } elseif ($carrier->getCreatedBy()->getID() != $user->getID()) {
            return "New carrier createdBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$carrier->getCreatedBy()->getID()}\".";
        } elseif (!is_null($carrier->getClosedAt())) {
            return "New carrier closedAt value should be null.";
        } elseif (!is_null($carrier->getClosedBy())) {
            return "New carrier closedBy value should be null.";
        } elseif (!$carrier->isActive()) {
            return "New carrier should be active.";
        }

        return true;
    }

    public static function createNewCarrierWithSupervisor(): bool|string {
        $user = TestHelpers::createTestUser();
        $fullName = "Test Carrier";
        $shortName = "Test";
        $numberOfTrialTasks = 3;
        $numberOfPenaltyTasks = 5;
        $carrier = Carrier::createNew($fullName, $shortName, [$user], $numberOfTrialTasks, $numberOfPenaltyTasks, $user);
        $supervisors = $carrier->getSupervisors();

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($carrier, Carrier::class)) {
            return "Expected a ".Carrier::class." object. Found: ".gettype($carrier).".";
        } elseif ($carrier->getFullName() != $fullName) {
            return "New carrier full name is incorrect. Expected: \"$fullName\", found: \"{$carrier->getFullName()}\".";
        } elseif ($carrier->getShortName() != $shortName) {
            return "New carrier short name is incorrect. Expected: \"$shortName\", found: \"{$carrier->getShortName()}\".";
        } elseif (count($supervisors) != 1) {
            return "New carrier has incorrect number of supervisors. Expected: 1, found: ".count($supervisors).".";
        } elseif ($supervisors[0]->getID() != $user->getID()) {
            return "New carrier supervisor user ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$supervisors[0]->getID()}\".";
        } elseif ($carrier->getNumberOfTrialTasks() != $numberOfTrialTasks) {
            return "New carrier numberOfTrialTasks value is incorrect. Expected: $numberOfTrialTasks, found: {$carrier->getNumberOfTrialTasks()}.";
        } elseif ($carrier->getNumberOfPenaltyTasks() != $numberOfPenaltyTasks) {
            return "New carrier numberOfPenaltyTasks value is incorrect. Expected: $numberOfPenaltyTasks, found: {$carrier->getNumberOfPenaltyTasks()}.";
        } elseif ($carrier->getCreatedBy()->getID() != $user->getID()) {
            return "New carrier createdBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$carrier->getCreatedBy()->getID()}\".";
        } elseif (!is_null($carrier->getClosedAt())) {
            return "New carrier closedAt value should be null.";
        } elseif (!is_null($carrier->getClosedBy())) {
            return "New carrier closedBy value should be null.";
        } elseif (!$carrier->isActive()) {
            return "New carrier should be active.";
        }

        return true;
    }

    public static function getCarrier(): bool|string {
        $user = TestHelpers::createTestUser();
        $fullName = "Test Carrier";
        $shortName = "Test";
        $numberOfTrialTasks = 3;
        $numberOfPenaltyTasks = 5;
        $carrier = Carrier::createNew($fullName, $shortName, [$user], $numberOfTrialTasks, $numberOfPenaltyTasks, $user);
        DatabaseEntity::removeFromCache($carrier);
        $carrier = Carrier::withID($carrier->getID());
        $supervisors = $carrier->getSupervisors();

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($carrier, Carrier::class)) {
            return "Expected a ".Carrier::class." object. Found: ".gettype($carrier).".";
        } elseif ($carrier->getFullName() != $fullName) {
            return "The carrier full name is incorrect. Expected: \"$fullName\", found: \"{$carrier->getFullName()}\".";
        } elseif ($carrier->getShortName() != $shortName) {
            return "The carrier short name is incorrect. Expected: \"$shortName\", found: \"{$carrier->getShortName()}\".";
        } elseif (count($supervisors) != 1) {
            return "The carrier has incorrect number of supervisors. Expected: 1, found: ".count($supervisors).".";
        } elseif ($supervisors[0]->getID() != $user->getID()) {
            return "The carrier supervisor user ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$supervisors[0]->getID()}\".";
        } elseif ($carrier->getNumberOfTrialTasks() != $numberOfTrialTasks) {
            return "The carrier numberOfTrialTasks value is incorrect. Expected: $numberOfTrialTasks, found: {$carrier->getNumberOfTrialTasks()}.";
        } elseif ($carrier->getNumberOfPenaltyTasks() != $numberOfPenaltyTasks) {
            return "The carrier numberOfPenaltyTasks value is incorrect. Expected: $numberOfPenaltyTasks, found: {$carrier->getNumberOfPenaltyTasks()}.";
        }

        return true;
    }

    public static function addSupervisor(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = Carrier::createNew("Test Carrier", "Test", [], 0, 0, $user);
        $supervisorsBeforeChange = $carrier->getSupervisors();
        $carrier->addSupervisor($user, $user);
        $supervisorsAfterChange = $carrier->getSupervisors();

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (count($supervisorsBeforeChange) != 0) {
            return "The number of carrier supervisors before the change is incorrect. Expected: 0, found: ".count($supervisorsBeforeChange).".";
        } elseif (count($supervisorsAfterChange) != 1) {
            return "The number of carrier supervisors after the change is incorrect. Expected: 1, found: ".count($supervisorsAfterChange).".";
        } elseif ($supervisorsAfterChange[0]->getID() != $user->getID()) {
            return "The carrier supervisor user ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$supervisorsAfterChange[0]->getID()}\".";
        }

        return true;
    }

    public static function removeSupervisor(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = Carrier::createNew("Test Carrier", "Test", [$user], 0, 0, $user);
        $supervisorsBeforeChange = $carrier->getSupervisors();
        $carrier->removeSupervisor($user, $user);
        $supervisorsAfterChange = $carrier->getSupervisors();

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (count($supervisorsBeforeChange) != 1) {
            return "The number of carrier supervisors before the change is incorrect. Expected: 1, found: ".count($supervisorsBeforeChange).".";
        } elseif ($supervisorsBeforeChange[0]->getID() != $user->getID()) {
            return "The carrier supervisor user ID is incorrect. Expected: \"{$user->getID()}\", found: \"{$supervisorsBeforeChange[0]->getID()}\".";
        } elseif (count($supervisorsAfterChange) != 0) {
            return "The number of carrier supervisors after the change is incorrect. Expected: 0, found: ".count($supervisorsAfterChange).".";
        }

        return true;
    }

    public static function throwExceptionWhenSettingNegativeNumberOfTrialTasks(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = Carrier::createNew("Test Carrier", "Test", [], 0, 0, $user);

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        try {
            $carrier->setNumberOfTrialTasks(-1);
        } catch (InvalidArgumentException) {
            return true;
        }

        return "No exception was thrown when setting negative number of trial tasks.";
    }

    public static function throwExceptionWhenSettingNegativeNumberOfPenaltyTasks(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = Carrier::createNew("Test Carrier", "Test", [], 0, 0, $user);

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        try {
            $carrier->setNumberOfPenaltyTasks(-1);
        } catch (InvalidArgumentException) {
            return true;
        }

        return "No exception was thrown when setting negative number of penalty tasks.";
    }

    public static function closeCarrier(): bool|string {
        $user = TestHelpers::createTestUser();
        $carrier = Carrier::createNew("Test Carrier", "Test", [], 0, 0, $user);
        $carrier->close($user);

        TestHelpers::deleteTestCarrierData($carrier->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (is_null($carrier->getClosedAt())) {
            return "The carrier closedAt value should not be null.";
        } elseif (is_null($carrier->getClosedBy())) {
            return "The carrier closedBy value should not be null.";
        } elseif($carrier->getClosedBy()->getID() != $user->getID()) {
            return "The carrier closedBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$carrier->getClosedBy()->getID()}\".";
        } elseif ($carrier->isActive()) {
            return "The carrier should be inactive.";
        }

        return true;
    }
}

?>