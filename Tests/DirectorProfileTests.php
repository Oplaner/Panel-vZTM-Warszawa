<?php

final class DirectorProfileTests {
    private const EXISTING_TEST_USER_LOGIN = 1387;

    public static function createNewDirectorProfileAndCheckItIsNotProtected(): bool|string {
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $profile = DirectorProfile::createNew($user, $user);

        self::deleteProfileDataAndTestUser($profile->getID(), $user->getID());

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
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $userID = $user->getID();
        $profile = DirectorProfile::createNew($user, $user);
        $profileID = $profile->getID();
        DatabaseEntity::removeFromCache($profile);
        $profile = DirectorProfile::withID($profileID);

        self::deleteProfileDataAndTestUser($profileID, $userID);

        if (!is_a($profile, DirectorProfile::class)) {
            return "Expected a ".DirectorProfile::class." object. Found: ".gettype($profile).".";
        } elseif (is_null($profile->getActivatedAt())) {
            return "Director profile activatedAt value should not be null.";
        } elseif ($profile->getActivatedBy()->getID() != $userID) {
            return "Director profile activatedBy value is incorrect. Expected (userID): \"$userID\", found (userID): \"{$profile->getActivatedBy()->getID()}\".";
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
        $user = User::createNew(self::EXISTING_TEST_USER_LOGIN);
        $profile = DirectorProfile::createNew($user, $user);
        $profile->deactivate($user);

        self::deleteProfileDataAndTestUser($profile->getID(), $user->getID());

        if (is_null($profile->getDeactivatedAt())) {
            return "Deactivated director profile deactivatedAt value should not be null.";
        } elseif (is_null($profile->getDeactivatedBy())) {
            return "Deactivated director profile deactivatedBy value should not be null.";
        } elseif ($profile->getDeactivatedBy()->getID() != $user->getID()) {
            return "Deactivated director profile deactivatedBy value is incorrect. Expected (userID): \"{$user->getID()}\", found (userID): \"{$profile->getDeactivatedBy()->getID()}\".";
        }

        return true;
    }

    private static function deleteProfileDataAndTestUser(string $profileID, string $userID): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM profiles_director
            WHERE profile_id = ?",
            [
                $profileID
            ]
        );
        $db->execute_query(
            "DELETE FROM profiles
            WHERE id = ?",
            [
                $profileID
            ]
        );
        $db->execute_query(
            "DELETE FROM users
            WHERE id = ?",
            [
                $userID
            ]
        );
    }
}

?>