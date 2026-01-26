<?php

final class ApplicationTests {
    public static function throwExceptionWhenCreatingApplicationWhenOneIsCurrentlyCreated(): bool|string {
        TestHelpers::createTestApplication(false);

        try {
            TestHelpers::createTestApplication(false);
        } catch (DomainException) {
            return true;
        }

        return "No exception was thrown when creating an application when one is currently in \"CREATED\" status.";
    }

    public static function throwExceptionWhenCreatingApplicationWhenOneIsCurrentlySent(): bool|string {
        TestHelpers::createTestApplication(true);

        try {
            TestHelpers::createTestApplication(false);
        } catch (DomainException) {
            return true;
        }

        return "No exception was thrown when creating an application when one is currently in \"SENT\" status.";
    }

    public static function createNewApplication(): bool|string {
        $login = TestHelpers::EXISTING_TEST_USER_LOGIN;
        $username = TestHelpers::EXISTING_TEST_USER_USERNAME;
        $dateOfBirth = new SystemDateTime("1998-02-10");
        $passedExamProofURL = "http://some-url.com";
        $motivation = "Motivation.";
        $status = ApplicationStatus::created;
        $application = Application::createNew($login, $username, $dateOfBirth, $passedExamProofURL, $motivation);
        $validationCode = TestHelpers::getApplicationValidationCode($application);

        if (!is_a($application, Application::class)) {
            return "Expected an ".Application::class." object. Found: ".gettype($application).".";
        } elseif ($application->getLogin() != $login) {
            return "New application login is incorrect. Expected: $login, found: {$application->getLogin()}.";
        } elseif ($application->getUsername() != $username) {
            return "New application username is incorrect. Expected: \"$username\", found: \"{$application->getUsername()}\".";
        } elseif ($application->getDateOfBirth() != $dateOfBirth) {
            $expected = $dateOfBirth->toLocalizedString(SystemDateTimeFormat::dateOnly);
            $found = $application->getDateOfBirth()->toLocalizedString(SystemDateTimeFormat::dateOnly);
            return "New application dateOfBirth is incorrect. Expected: $expected, found: $found.";
        } elseif ($application->getPassedExamProofURL() != $passedExamProofURL) {
            return "New application passedExamProofURL is incorrect. Expected: \"$passedExamProofURL\", found: \"{$application->getPassedExamProofURL()}\".";
        } elseif ($application->getMotivation() != $motivation) {
            return "New application motivation is incorrect. Expected: \"$motivation\", found: \"{$application->getMotivation()}\".";
        } elseif ($application->getStatus() != $status) {
            return "New application status is incorrect. Expected: \"{$status->value}\", found: \"{$application->getStatus()->value}\".";
        } elseif (is_null($validationCode)) {
            return "New application validationCode should not be null.";
        } elseif (!is_null($application->getAssignedCarrier())) {
            return "New application assignedCarrier should be null.";
        } elseif (!is_null($application->getResolutionNote())) {
            return "New application resolutionNote should be null.";
        } elseif (!is_null($application->getResolvedAt())) {
            return "New application resolvedAt value should be null.";
        } elseif (!is_null($application->getResolvedBy())) {
            return "New application resolvedBy value should be null.";
        }

        return true;
    }

    public static function getApplication(): bool|string {
        $login = TestHelpers::EXISTING_TEST_USER_LOGIN;
        $username = TestHelpers::EXISTING_TEST_USER_USERNAME;
        $dateOfBirth = new SystemDateTime("1998-02-10");
        $passedExamProofURL = "http://some-url.com";
        $motivation = "Motivation.";
        $application = Application::createNew($login, $username, $dateOfBirth, $passedExamProofURL, $motivation);
        $validationCode = TestHelpers::getApplicationValidationCode($application);
        $application->send($validationCode);
        $resolver = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($resolver);
        $resolutionNote = "Approved.";
        $application->approve($carrier, $resolutionNote, $resolver);
        $status = ApplicationStatus::approved;
        DatabaseEntity::removeFromCache($application);
        $application = Application::withID($application->getID());

        if (!is_a($application, Application::class)) {
            return "Expected an ".Application::class." object. Found: ".gettype($application).".";
        } elseif ($application->getLogin() != $login) {
            return "The application login is incorrect. Expected: \"$login\", found: \"{$application->getLogin()}\".";
        } elseif ($application->getUsername() != $username) {
            return "The application username is incorrect. Expected: \"$username\", found: \"{$application->getUsername()}\".";
        } elseif ($application->getDateOfBirth() != $dateOfBirth) {
            $expected = $dateOfBirth->toLocalizedString(SystemDateTimeFormat::dateOnly);
            $found = $application->getDateOfBirth()->toLocalizedString(SystemDateTimeFormat::dateOnly);
            return "The application dateOfBirth is incorrect. Expected: $expected, found: $found.";
        } elseif ($application->getPassedExamProofURL() != $passedExamProofURL) {
            return "The application passedExamProofURL is incorrect. Expected: \"$passedExamProofURL\", found: \"{$application->getPassedExamProofURL()}\".";
        } elseif ($application->getMotivation() != $motivation) {
            return "The application motivation is incorrect. Expected: \"$motivation\", found: \"{$application->getMotivation()}\".";
        } elseif ($application->getStatus() != $status) {
            return "The application status is incorrect. Expected: \"{$status->value}\", found: \"{$application->getStatus()->value}\".";
        } elseif ($application->getAssignedCarrier()->getID() != $carrier->getID()) {
            return "The application assignedCarrier ID is incorrect. Expected: \"{$carrier->getID()}\", found: \"{$application->getAssignedCarrier()->getID()}\".";
        } elseif ($application->getResolutionNote() != $resolutionNote) {
            return "The application resolutionNote is incorrect. Expected: \"$resolutionNote\", found: \"{$application->getResolutionNote()}\".";
        } elseif ($application->getResolvedBy()->getID() != $resolver->getID()) {
            return "The application resolvedBy user ID is incorrect. Expected: \"{$resolver->getID()}\", found: \"{$application->getResolvedBy()->getID()}\".";
        }

        return true;
    }

    public static function sendMessageWithValidationCodeWhenNewApplicationIsCreated(): bool|string {
        $application = TestHelpers::createTestApplication(false);
        $validationCode = TestHelpers::getApplicationValidationCode($application);
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT COUNT(*)
            FROM mybb18_privatemessages
            WHERE uid = ?
            AND fromid = ?
            AND toid = ?
            AND message LIKE ?",
            [
                $application->getLogin(),
                589,
                $application->getLogin(),
                "%$validationCode%"
            ]
        );
        $count = $result->fetch_column();
        $result->free();

        if ($count == 0) {
            return "Message with validation code was not sent.";
        }

        return true;
    }

    public static function expireStaleCreatedApplications(): bool|string {
        $application = TestHelpers::createTestApplication(false);
        $initialStatus = $application->getStatus();
        $properties = PropertiesReader::getProperties("application");
        $validityDays = $properties["createdApplicationValidityDays"];
        $validityHours = $properties["createdApplicationValidityHours"];
        $validityMinutes = $properties["createdApplicationValidityMinutes"];
        $expiredValidityDateTime = $application
            ->getCreatedAt()
            ->subtracting($validityDays, $validityHours, $validityMinutes)
            ->toDatabaseString();
        DatabaseConnector::shared()->execute_query(
            "UPDATE applications
            SET created_at = ?
            WHERE id = ?",
            [
                $expiredValidityDateTime,
                $application->getID()
            ]
        );
        Application::getAll(); // Trigger expiration check.
        DatabaseEntity::removeFromCache($application);
        $application = Application::withID($application->getID());
        $updatedStatus = $application->getStatus();

        if ($initialStatus != ApplicationStatus::created) {
            $expected = ApplicationStatus::created->value;
            $found = $initialStatus->value;
            return "Initial application status is incorrect. Expected: \"$expected\", found: \"$found\".";
        } elseif ($updatedStatus != ApplicationStatus::expired) {
            $expected = ApplicationStatus::expired->value;
            $found = $updatedStatus->value;
            return "Updated application status is incorrect. Expected: \"$expected\", found: \"$found\".";
        }

        return true;
    }

    public static function throwExceptionWhenSendingApplicationWithIncorrectValidationCode(): bool|string {
        $application = TestHelpers::createTestApplication(false);
        $validationCode = TestHelpers::getApplicationValidationCode($application)."0";

        try {
            $application->send($validationCode);
        } catch (DomainException) {
            return true;
        }

        return "No exception was thrown when sending application with incorrect validation code.";
    }

    public static function sendApplication(): bool|string {
        $application = TestHelpers::createTestApplication(false);
        $validationCode = TestHelpers::getApplicationValidationCode($application);
        $application->send($validationCode);
        $validationCode = TestHelpers::getApplicationValidationCode($application);
        $status = $application->getStatus();

        if (!is_null($validationCode)) {
            return "Validation code should be null after sending application.";
        } elseif ($status != ApplicationStatus::sent) {
            $expected = ApplicationStatus::sent->value;
            $found = $status->value;
            return "The application status is incorrect. Expected: \"$expected\", found: \"$found\".";
        }

        return true;
    }

    public static function throwExceptionWhenResolvingApplicationWithStatusCreated(): bool|string {
        $application = TestHelpers::createTestApplication(false);
        $resolver = TestHelpers::createTestUser();

        try {
            $application->reject("Rejected.", $resolver);
        } catch (DomainException) {
            return true;
        }

        return "No exception was thrown when resolving application with status \"CREATED\".";
    }

    public static function acceptApplication(): bool|string {
        $application = TestHelpers::createTestApplication(true);
        $resolver = TestHelpers::createTestUser();
        $carrier = TestHelpers::createTestCarrier($resolver);
        $resolutionNote = "Approved.";
        $application->approve($carrier, $resolutionNote, $resolver);
        $status = ApplicationStatus::approved;

        if ($application->getStatus() != $status) {
            return "The application status is incorrect. Expected: \"{$status->value}\", found: \"{$application->getStatus()->value}\".";
        } elseif ($application->getAssignedCarrier()->getID() != $carrier->getID()) {
            return "The application assignedCarrier ID is incorrect. Expected: \"{$carrier->getID()}\", found: \"{$application->getAssignedCarrier()->getID()}\".";
        } elseif ($application->getResolutionNote() != $resolutionNote) {
            return "The application resolutionNote is incorrect. Expected: \"$resolutionNote\", found: \"{$application->getResolutionNote()}\".";
        } elseif ($application->getResolvedBy()->getID() != $resolver->getID()) {
            return "The application resolvedBy user ID is incorrect. Expected: \"{$resolver->getID()}\", found: \"{$application->getResolvedBy()->getID()}\".";
        }

        return true;
    }

    public static function rejectApplication(): bool|string {
        $application = TestHelpers::createTestApplication(true);
        $resolver = TestHelpers::createTestUser();
        $resolutionNote = "Rejected.";
        $application->reject($resolutionNote, $resolver);
        $status = ApplicationStatus::rejected;

        if ($application->getStatus() != $status) {
            return "The application status is incorrect. Expected: \"{$status->value}\", found: \"{$application->getStatus()->value}\".";
        } elseif ($application->getResolutionNote() != $resolutionNote) {
            return "The application resolutionNote is incorrect. Expected: \"$resolutionNote\", found: \"{$application->getResolutionNote()}\".";
        } elseif ($application->getResolvedBy()->getID() != $resolver->getID()) {
            return "The application resolvedBy user ID is incorrect. Expected: \"{$resolver->getID()}\", found: \"{$application->getResolvedBy()->getID()}\".";
        }

        return true;
    }
}

?>