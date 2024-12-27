<?php

final class DirectorProfileTests {
    public static function createNewDirectorProfileAndCheckItIsNotProtected(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DirectorProfile::createNew($user, $user);

        TestHelpers::deleteTestDirectorProfileData($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile, DirectorProfile::class)) {
            return "Expected a ".DirectorProfile::class." object. Found: ".gettype($profile).".";
        } elseif (is_null($profile->getActivatedAt())) {
            return "Director profile activatedAt value should not be null.";
        } elseif ($profile->getActivatedBy() !== $user) {
            return "Director profile activatedBy value is incorrect. Expected (userID): \"{$user->getID()}\", found (userID): \"{$profile->getActivatedBy()->getID()}\".";
        } elseif (!is_null($profile->getDeactivatedAt())) {
            return "New director profile deactivatedAt value should be null.";
        } elseif (!is_null($profile->getDeactivatedBy())) {
            return "New director profile deactivatedby value should be null.";
        } elseif ($profile->isProtected()) {
            return "New director profile isProtected value should be false.";
        }

        return true;
    }

    public static function getDirectorProfile(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DirectorProfile::createNew($user, $user);
        DatabaseEntity::removeFromCache($profile);
        $profile = DirectorProfile::withID($profile->getID());

        TestHelpers::deleteTestDirectorProfileData($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (!is_a($profile, DirectorProfile::class)) {
            return "Expected a ".DirectorProfile::class." object. Found: ".gettype($profile).".";
        } elseif (is_null($profile->getActivatedAt())) {
            return "Director profile activatedAt value should not be null.";
        } elseif ($profile->getActivatedBy()->getID() != $user->getID()) {
            return "Director profile activatedBy value is incorrect. Expected (userID): \"{$user->getID()}\", found (userID): \"{$profile->getActivatedBy()->getID()}\".";
        } elseif (!is_null($profile->getDeactivatedAt())) {
            return "Director profile deactivatedAt value should be null.";
        } elseif (!is_null($profile->getDeactivatedBy())) {
            return "Director profile deactivatedby value should be null.";
        } elseif ($profile->isProtected()) {
            return "Director profile isProtected value should be false.";
        }

        return true;
    }

    public static function deactivateDirectorProfile(): bool|string {
        $user = TestHelpers::createTestUser();
        $profile = DirectorProfile::createNew($user, $user);
        $profile->deactivate($user);

        TestHelpers::deleteTestDirectorProfileData($profile->getID());
        TestHelpers::deleteTestUser($user->getID());

        if (is_null($profile->getDeactivatedAt())) {
            return "Deactivated director profile deactivatedAt value should not be null.";
        } elseif (is_null($profile->getDeactivatedBy())) {
            return "Deactivated director profile deactivatedBy value should not be null.";
        } elseif ($profile->getDeactivatedBy()->getID() != $user->getID()) {
            return "Deactivated director profile deactivatedBy value is incorrect. Expected (userID): \"{$user->getID()}\", found (userID): \"{$profile->getDeactivatedBy()->getID()}\".";
        }

        return true;
    }
}

?>