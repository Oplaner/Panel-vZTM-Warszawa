<?php

final class ApplicationController extends Controller {
    private const CANDIDATE_LOGIN_FIELD_NAME = "Kandydat";
    private const DATE_OF_BIRTH_FIELD_NAME = "Data urodzenia";
    private const PASSED_EXAM_PROOF_URL_FIELD_NAME = "Link do wyniku egzaminu WORD";
    private const MOTIVATION_FIELD_NAME = "Motywacja";

    #[Route("/applications/new", RequestMethod::get)]
    #[Access(
        group: AccessGroup::guestsOnly
    )]
    public function showNewApplicationForm(): void {
        Application::expireStaleCreatedApplications();
        $dateOfBirthYearSpan = $this->getDateOfBirthYearSpan();
        $parameters = [
            "candidateSelection" => null,
            "candidateLogin" => "",
            "day" => 0,
            "month" => 0,
            "year" => 0,
            "maxYear" => $dateOfBirthYearSpan["maxYear"],
            "minYear" => $dateOfBirthYearSpan["minYear"],
            "passedExamProofURL" => "",
            "motivation" => ""
        ];
        self::renderView(View::applicationNew, $parameters);
    }

    #[Route("/applications/new", RequestMethod::post)]
    #[Access(
        group: AccessGroup::guestsOnly
    )]
    public function addNewApplication(array $input): void {
        $post = $input[Router::POST_DATA_KEY];
        $candidateLogin = InputValidator::clean($post["candidateLogin"]);
        $day = InputValidator::clean($post["day"] ?? null) ?? 0;
        $month = InputValidator::clean($post["month"] ?? null) ?? 0;
        $year = InputValidator::clean($post["year"] ?? null) ?? 0;
        $passedExamProofURL = InputValidator::clean($post["passedExamProofURL"]);
        $motivation = InputValidator::clean($post["motivation"]);
        $dateOfBirthYearSpan = $this->getDateOfBirthYearSpan();
        $maxYear = $dateOfBirthYearSpan["maxYear"];
        $minYear = $dateOfBirthYearSpan["minYear"];
        $candidateSelection = null;

        $errors = [];
        $messageType = null;
        $message = null;

        Logger::log(LogLevel::info, "Validating new application information.");

        // Validation: candidate login.
        try {
            InputValidator::checkNonEmpty($candidateLogin, self::CANDIDATE_LOGIN_FIELD_NAME);
            InputValidator::checkInteger($candidateLogin, 0, 4294967295, self::CANDIDATE_LOGIN_FIELD_NAME);
            $candidateSelections = User::getLoginAndUsernamePairsForAnyUsersWithLogins([$candidateLogin]);

            if (empty($candidateSelections)) {
                throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::CANDIDATE_LOGIN_FIELD_NAME));
            }

            $candidateSelection = $candidateSelections[0];
            $applications = Application::getAllByLogin($candidateLogin, "created_at DESC", "1");
            $latestApplication = empty($applications) ? null : $applications[0];
            $activeOrAwaitingStatuses = [
                ApplicationStatus::approved,
                ApplicationStatus::created,
                ApplicationStatus::sent
            ];

            if (!is_null($latestApplication) && in_array($latestApplication->getStatus(), $activeOrAwaitingStatuses)) {
                throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::CANDIDATE_LOGIN_FIELD_NAME));
            }
        } catch (ValidationException $exception) {
            $candidateLogin = "";
            $candidateSelection = null;
            $errors[] = $exception->getMessage();
        }

        // Validation: birth date.
        try {
            InputValidator::checkNonEmpty($day);
            InputValidator::checkNonEmpty($month);
            InputValidator::checkNonEmpty($year);
            InputValidator::checkInteger($day, 1, 31);
            InputValidator::checkInteger($month, 1, 12);
            InputValidator::checkInteger($year, $minYear, $maxYear);
            InputValidator::checkDateComponents($day, $month, $year);
        } catch (ValidationException) {
            $errors[] = InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_DATE, self::DATE_OF_BIRTH_FIELD_NAME);
        }

        // Validation: passed exam proof URL.

        try {
            InputValidator::checkNonEmpty($passedExamProofURL, self::PASSED_EXAM_PROOF_URL_FIELD_NAME);
            InputValidator::checkURL($passedExamProofURL, self::PASSED_EXAM_PROOF_URL_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: motivation.

        try {
            InputValidator::checkNonEmpty($motivation, self::MOTIVATION_FIELD_NAME);
            InputValidator::checkLength($motivation, 100, 500, self::MOTIVATION_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        if (!empty($errors)) {
            Logger::log(LogLevel::info, "Validation failed.");
            $messageType = "error";
            $message = self::makeErrorMessage($errors);
        } else {
            Logger::log(LogLevel::info, "Validation succeeded.");
            $dateOfBirth = new SystemDateTime("$year-$month-$day");
            Application::createNew($candidateLogin, $dateOfBirth, $passedExamProofURL, $motivation);
            $messageType = "success";
            $message = "<span>Aplikacja została utworzona. Przejdź teraz do swojej <a href=\"https://vztmforum.waw.pl/new/private.php?fid=0\">skrzynki odbiorczej</a> na forum. Znajdziesz tam wiadomość z linkiem weryfikacyjnym. Nie martw się, otrzymasz ją nawet wtedy, gdy skrzynka jest przepełniona. Pamiętaj jednak, że link ma ograniczoną ważność.</span>";
            $candidateLogin = "";
            $candidateSelection = null;
            $day = 0;
            $month = 0;
            $year = 0;
            $passedExamProofURL = "";
            $motivation = "";
        }

        $parameters = [
            "showMessage" => true,
            "messageType" => $messageType,
            "message" => $message,
            "candidateLogin" => $candidateLogin,
            "candidateSelection" => $candidateSelection,
            "day" => $day,
            "month" => $month,
            "year" => $year,
            "maxYear" => $maxYear,
            "minYear" => $minYear,
            "passedExamProofURL" => $passedExamProofURL,
            "motivation" => $motivation
        ];
        self::renderView(View::applicationNew, $parameters);
    }

    private function getDateOfBirthYearSpan(): array {
        $properties = PropertiesReader::getProperties("application");
        $maxYear = (int) SystemDateTime::now()->toLocalizedString(SystemDateTimeFormat::year);
        $minYear = $maxYear - $properties["dateOfBirthMinYearOffset"];
        return [
            "maxYear" => $maxYear,
            "minYear" => $minYear
        ];
    }
}

?>