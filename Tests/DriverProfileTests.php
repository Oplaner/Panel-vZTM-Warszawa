<?php

final class DriverProfileTests {
    public static function throwExceptionWhenCreatingDriverProfileWhenOneIsCurrentlyActive(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DriverProfile::createNew($user, $user);
        $didThrowException = false;

        try {
            DriverProfile::createNew($user, $user);
        } catch (Exception $exception) {
            $didThrowException = true;
        }

        TestHelpers::deleteTestDriverProfile($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($didThrowException) {
            return true;
        } else {
            return "No exception was thrown when creating new driver profile when one is currently active for the user.";
        }
    }

    public static function createNewDriverProfileFirstForUser(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DriverProfile::createNew($user, $user);

        TestHelpers::deleteTestDriverProfile($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile, DriverProfile::class)) {
            return "Expected a ".DriverProfile::class." object. Found: ".gettype($profile).".";
        } elseif ($profile->getInitialPenaltyMultiplier() != 0) {
            return "The user's first driver profile initialPenaltyMultiplier value is incorrect. Expected: 0, found: {$profile->getInitialPenaltyMultiplier()}.";
        } elseif (!is_null($profile->getAcquiredPenaltyMultiplier())) {
            return "New driver profile acquiredPenaltyMultiplier value should be null.";
        } elseif (is_null($profile->getActivatedAt())) {
            return "New driver profile activatedAt value should not be null.";
        } elseif ($profile->getActivatedBy()->getID() != $user->getID()) {
            return "New driver profile activatedBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$profile->getActivatedBy()->getID()}\".";
        } elseif (!is_null($profile->getDeactivatedAt())) {
            return "New driver profile deactivatedAt value should be null.";
        } elseif (!is_null($profile->getDeactivatedBy())) {
            return "New driver profile deactivatedBy value should be null.";
        } elseif (!$profile->isActive()) {
            return "New driver profile should be active.";
        }

        return true;
    }

    public static function createNewDriverProfileWhenUserHasOneInactiveWithoutAcquiredPenalty(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile1 = DriverProfile::createNew($user, $user);
        $profile1->deactivate($user);
        $profile2 = DriverProfile::createNew($user, $user);

        TestHelpers::deleteTestDriverProfile($profile1->getID());
        TestHelpers::deleteTestDriverProfile($profile2->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile1, DriverProfile::class)) {
            return "Expected a ".DriverProfile::class." object. Found: ".gettype($profile1).".";
        } elseif (is_null($profile2)) {
            return "The second driver profile should be created.";
        } elseif ($profile2->getInitialPenaltyMultiplier() != $profile1->getAcquiredPenaltyMultiplier()) {
            return "The second driver profile initialPenaltyMultiplier value is incorrect. Expected: {$profile1->getAcquiredPenaltyMultiplier()}, found: {$profile2->getInitialPenaltyMultiplier()}.";
        } elseif (!is_null($profile2->getAcquiredPenaltyMultiplier())) {
            return "The second driver profile acquiredPenaltyMultiplier value should be null.";
        } elseif (is_null($profile2->getActivatedAt())) {
            return "The second driver profile activatedAt value should not be null.";
        } elseif ($profile2->getActivatedBy()->getID() != $user->getID()) {
            return "The second driver profile activatedBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$profile2->getActivatedBy()->getID()}\".";
        } elseif (!is_null($profile2->getDeactivatedAt())) {
            return "The second driver profile deactivatedAt value should be null.";
        } elseif (!is_null($profile2->getDeactivatedBy())) {
            return "The second driver profile deactivatedBy value should be null.";
        } elseif (!$profile2->isActive()) {
            return "The second driver profile should be active.";
        }

        return true;
    }

    public static function createNewDriverProfileWhenUserHasOneInactiveWithAcquiredPenalty(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile1 = DriverProfile::createNew($user, $user);
        $profile1->incrementPenaltyMultiplier();
        $profile1->deactivate($user);
        $profile2 = DriverProfile::createNew($user, $user);

        TestHelpers::deleteTestDriverProfile($profile1->getID());
        TestHelpers::deleteTestDriverProfile($profile2->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile1, DriverProfile::class)) {
            return "Expected a ".DriverProfile::class." object. Found: ".gettype($profile1).".";
        } elseif (is_null($profile2)) {
            return "The second driver profile should be created.";
        } elseif ($profile2->getInitialPenaltyMultiplier() != $profile1->getAcquiredPenaltyMultiplier()) {
            return "The second driver profile initialPenaltyMultiplier value is incorrect. Expected: {$profile1->getAcquiredPenaltyMultiplier()}, found: {$profile2->getInitialPenaltyMultiplier()}.";
        } elseif (!is_null($profile2->getAcquiredPenaltyMultiplier())) {
            return "The second driver profile acquiredPenaltyMultiplier value should be null.";
        } elseif (is_null($profile2->getActivatedAt())) {
            return "The second driver profile activatedAt value should not be null.";
        } elseif ($profile2->getActivatedBy()->getID() != $user->getID()) {
            return "The second driver profile activatedBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$profile2->getActivatedBy()->getID()}\".";
        } elseif (!is_null($profile2->getDeactivatedAt())) {
            return "The second driver profile deactivatedAt value should be null.";
        } elseif (!is_null($profile2->getDeactivatedBy())) {
            return "The second driver profile deactivatedBy value should be null.";
        } elseif (!$profile2->isActive()) {
            return "The second driver profile should be active.";
        }

        return true;
    }

    public static function getDriverProfile(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DriverProfile::createNew($user, $user);
        DatabaseEntity::removeFromCache($profile);
        $profile = DriverProfile::withID($profile->getID());

        TestHelpers::deleteTestDriverProfile($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile, DriverProfile::class)) {
            return "Expected a ".DriverProfile::class." object. Found: ".gettype($profile).".";
        } elseif ($profile->getInitialPenaltyMultiplier() != 0) {
            return "The user's first driver profile initialPenaltyMultiplier value is incorrect. Expected: 0, found: {$profile->getInitialPenaltyMultiplier()}.";
        } elseif (is_null($profile->getActivatedAt())) {
            return "The driver profile activatedAt value should not be null.";
        } elseif ($profile->getActivatedBy()->getID() != $user->getID()) {
            return "The driver profile activatedBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$profile->getActivatedBy()->getID()}\".";
        }

        return true;
    }

    public static function doNotUpdateAcquiredPenaltyMultiplierWhenDriverProfileIsInactive(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DriverProfile::createNew($user, $user);
        $profile->deactivate($user);
        $valueBeforeChange = $profile->getAcquiredPenaltyMultiplier();
        $profile->incrementPenaltyMultiplier();
        $valueAfterChange = $profile->getAcquiredPenaltyMultiplier();

        TestHelpers::deleteTestDriverProfile($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueAfterChange != $valueBeforeChange) {
            return "Deactivated driver profile acquiredPenaltyMultiplier value should not change.";
        }

        return true;
    }

    public static function updateAcquiredPenaltyMultiplierWhenDriverProfileIsActive(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DriverProfile::createNew($user, $user);
        $valueBeforeChange = $profile->getAcquiredPenaltyMultiplier();
        $profile->incrementPenaltyMultiplier();
        $valueAfterChange = $profile->getAcquiredPenaltyMultiplier();
        $expectedValue = $profile->getInitialPenaltyMultiplier() + 1;

        TestHelpers::deleteTestDriverProfile($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if ($valueAfterChange == $valueBeforeChange) {
            return "Active driver profile acquiredPenaltyMultiplier value should change.";
        } elseif ($valueAfterChange != $expectedValue) {
            return "Driver profile acquiredPenaltyMultiplier value is incorrect. Expected: $expectedValue, found: $valueAfterChange.";
        }

        return true;
    }

    public static function deactivateDriverProfile(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DriverProfile::createNew($user, $user);
        $profile->deactivate($user);

        TestHelpers::deleteTestDriverProfile($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (is_null($profile->getDeactivatedAt())) {
            return "Deactivated driver profile deactivatedAt value should not be null.";
        } elseif (is_null($profile->getDeactivatedBy())) {
            return "Deactivated driver profile deactivatedBy value should not be null.";
        } elseif ($profile->getDeactivatedBy()->getID() != $user->getID()) {
            return "Deactivated driver profile deactivatedBy user ID value is incorrect. Expected: \"{$user->getID()}\", found: \"{$profile->getDeactivatedBy()->getID()}\".";
        } elseif ($profile->isActive()) {
            return "Deactivated driver profile should be inactive.";
        }

        return true;
    }
}

?>