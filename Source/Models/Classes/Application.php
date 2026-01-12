<?php

final class Application extends DatabaseEntity {
    private const VALIDATION_CODE_CHARACTERS = "346789ABCDEFGHJKLMNPQRTUVWXY";
    private const VALIDATION_CODE_LENGTH = 6;

    private int $login;
    private string $username;
    private SystemDateTime $dateOfBirth;
    private string $passedExamProofURL;
    private string $motivation;
    private SystemDateTime $createdAt;
    private ApplicationStatus $status;
    private ?string $validationCode;
    private ?Carrier $assignedCarrier;
    private ?string $resolutionNote;
    private ?SystemDateTime $resolvedAt;
    private ?User $resolvedBy;

    private function __construct(?string $id, int $login, string $username, SystemDateTime $dateOfBirth, string $passedExamProofURL, string $motivation, SystemDateTime $createdAt, ApplicationStatus $status, ?string $validationCode, ?Carrier $assignedCarrier, ?string $resolutionNote, ?SystemDateTime $resolvedAt, ?User $resolvedBy) {
        parent::__construct($id);
        $this->login = $login;
        $this->username = $username;
        $this->dateOfBirth = $dateOfBirth;
        $this->passedExamProofURL = $passedExamProofURL;
        $this->motivation = $motivation;
        $this->createdAt = $createdAt;
        $this->status = $status;
        $this->validationCode = $validationCode;
        $this->assignedCarrier = $assignedCarrier;
        $this->resolutionNote = $resolutionNote;
        $this->resolvedAt = $resolvedAt;
        $this->resolvedBy = $resolvedBy;
        $this->save();
    }

    public static function createNew(int $login, string $username, SystemDateTime $dateOfBirth, string $passedExamProofURL, string $motivation): Application {
        Logger::log(LogLevel::info, "Creating new application for user with login $login.");
        self::validateActiveApplicationDoesNotExist($login);
        $validationCode = self::generateValidationCode();
        $application = new Application(null, $login, $username, $dateOfBirth, $passedExamProofURL, $motivation, SystemDateTime::now(), ApplicationStatus::created, $validationCode, null, null, null, null);
        self::sendMessageWithValidationCode($login, $validationCode);
        return $application;
    }

    public static function withID(string $id): ?Application {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, Application::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT id, login, username, date_of_birth, passed_exam_proof_url, motivation, created_at, status, validation_code, assigned_carrier_id, resolution_note, resolved_at, resolved_by_user_id
            FROM applications
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find application with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $login = $data["login"];
        $username = $data["username"];
        $dateOfBirth = new SystemDateTime($data["date_of_birth"]);
        $passedExamProofURL = $data["passed_exam_proof_url"];
        $motivation = $data["motivation"];
        $createdAt = new SystemDateTime($data["created_at"]);
        $status = ApplicationStatus::from($data["status"]);
        $validationCode = $data["validation_code"];
        $assignedCarrier = is_null($data["assigned_carrier_id"]) ? null : Carrier::withID($data["assigned_carrier_id"]);
        $resolutionNote = $data["resolution_note"];
        $resolvedAt = is_null($data["resolved_at"]) ? null : new SystemDateTime($data["resolved_at"]);
        $resolvedBy = is_null($data["resolved_by_user_id"]) ? null : User::withID($data["resolved_by_user_id"]);
        return new Application($id, $login, $username, $dateOfBirth, $passedExamProofURL, $motivation, $createdAt, $status, $validationCode, $assignedCarrier, $resolutionNote, $resolvedAt, $resolvedBy);
    }

    public static function getAll(string $sortSubstring = "created_at ASC", ?string $limitSubstring = null): array {
        self::expireStaleCreatedApplications();
        $limitString = self::makeQueryLimitString($limitSubstring);
        $query = trim(
            "SELECT id
            FROM applications
            ORDER BY $sortSubstring
            $limitString"
        );
        return self::getWithQuery($query);
    }

    public static function getSent(string $sortSubstring = "created_at ASC", ?string $limitSubstring = null): array {
        self::expireStaleCreatedApplications();
        $limitString = self::makeQueryLimitString($limitSubstring);
        $query = trim(
            "SELECT id
            FROM applications
            WHERE status = ?
            ORDER BY $sortSubstring
            $limitString"
        );
        $parameters = [
            ApplicationStatus::sent->value
        ];
        return self::getWithQuery($query, $parameters);
    }

    public static function getCreatedOrSentCountByLogin(string $login): int {
        self::expireStaleCreatedApplications();
        $query =
            "SELECT COUNT(*)
            FROM applications
            WHERE login = ?
            AND status IN (?, ?)";
        $parameters = [
            $login,
            ApplicationStatus::created->value,
            ApplicationStatus::sent->value
        ];
        return self::getCountWithQuery($query, $parameters);
    }

    private static function validateActiveApplicationDoesNotExist(int $login): void {
        if (self::getCreatedOrSentCountByLogin($login) > 0) {
            Logger::log(LogLevel::warn, "Failed to create new application.");
            throw new DomainException("Cannot create new application - there is one currently active for the user.");
        }
    }

    private static function generateValidationCode(): string {
        $possibleCharacters = str_split(self::VALIDATION_CODE_CHARACTERS);
        $validationCodeCharacters = [];

        for ($i = 0; $i < self::VALIDATION_CODE_LENGTH; $i++) {
            $validationCodeCharacters[] = $possibleCharacters[rand(0, count($possibleCharacters) - 1)];
        }

        shuffle($validationCodeCharacters);
        return implode($validationCodeCharacters);
    }

    private static function sendMessageWithValidationCode(int $login, string $validationCode): void {
        $properties = PropertiesReader::getProperties("application");
        $validityDays = $properties["createdApplicationValidityDays"];
        $validityHours = $properties["createdApplicationValidityHours"];
        $validityMinutes = $properties["createdApplicationValidityMinutes"];
        $validityThreshold = SystemDateTime::now()
            ->adding($validityDays, $validityHours, $validityMinutes)
            ->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithoutSeconds);
        $subject = "Kod weryfikacyjny";
        $message = "Twój kod weryfikacyjny do potwierdzenia aplikacji na stanowisko Kierowcy vZTM Warszawa to: [b]{$validationCode}[/b]. Kod ważny jest do $validityThreshold.";
        MessageSender::sendMessage($login, $subject, $message);
    }

    private static function expireStaleCreatedApplications(): void {
        $properties = PropertiesReader::getProperties("application");
        $validityDays = $properties["createdApplicationValidityDays"];
        $validityHours = $properties["createdApplicationValidityHours"];
        $validityMinutes = $properties["createdApplicationValidityMinutes"];
        $validityThreshold = SystemDateTime::now()
            ->subtracting($validityDays, $validityHours, $validityMinutes)
            ->toDatabaseString();

        DatabaseConnector::shared()->execute_query(
            "UPDATE applications
            SET status = ?, validation_code = ?
            WHERE status = ?
            AND created_at <= ?",
            [
                ApplicationStatus::expired->value,
                null,
                ApplicationStatus::created->value,
                $validityThreshold
            ]
        );
    }

    public function getLogin(): int {
        return $this->login;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getDateOfBirth(): SystemDateTime {
        return $this->dateOfBirth;
    }

    public function getPassedExamProofURL(): string {
        return $this->passedExamProofURL;
    }

    public function getMotivation(): string {
        return $this->motivation;
    }

    public function getCreatedAt(): SystemDateTime {
        return $this->createdAt;
    }

    public function getStatus(): ApplicationStatus {
        return $this->status;
    }

    public function getAssignedCarrier(): ?Carrier {
        return $this->assignedCarrier;
    }

    public function getResolutionNote(): ?string {
        return $this->resolutionNote;
    }

    public function getResolvedAt(): ?SystemDateTime {
        return $this->resolvedAt;
    }

    public function getResolvedBy(): ?User {
        return $this->resolvedBy;
    }

    public function send(string $validationCode) {
        Logger::log(LogLevel::info, "Trying to send application with ID \"{$this->id}\".");

        if ($this->validationCode !== $validationCode) {
            Logger::log(LogLevel::warn, "Failed to validate provided code.");
            throw new DomainException("Provided validation code is incorrect.");
        }

        $this->status = ApplicationStatus::sent;
        $this->validationCode = null;
        $this->wasModified = true;
        $this->save();
        Logger::log(LogLevel::info, "Application status is now \"SENT\".");
    }

    public function approve(Carrier $assignedCarrier, ?string $resolutionNote, User $resolver): void {
        $this->resolve(ApplicationStatus::approved, $assignedCarrier, $resolutionNote, $resolver);
    }

    public function reject(string $resolutionNote, User $resolver): void {
        $this->resolve(ApplicationStatus::rejected, null, $resolutionNote, $resolver);
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", login: %d, username: \"%s\", dateOfBirth: %s, createdAt: %s, status: \"%s\", assignedCarrierID: %s, resolvedAt: %s, resolvedByUserID: %s)",
            $this->id,
            $this->login,
            $this->username,
            $this->dateOfBirth->toDatabaseString(true),
            $this->createdAt->toDatabaseString(),
            $this->status->value,
            is_null($this->assignedCarrier) ? "null" : "\"{$this->assignedCarrier->getID()}\"",
            is_null($this->resolvedAt) ? "null" : $this->resolvedAt->toDatabaseString(),
            is_null($this->resolvedBy) ? "null" : "\"{$this->resolvedBy->getID()}\""
        );
    }

    protected function save(): void {
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $db->execute_query(
                "INSERT INTO applications
                (id, login, username, date_of_birth, passed_exam_proof_url, motivation, created_at, status, validation_code, assigned_carrier_id, resolution_note, resolved_at, resolved_by_user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->login,
                    $this->username,
                    $this->dateOfBirth->toDatabaseString(true),
                    $this->passedExamProofURL,
                    $this->motivation,
                    $this->createdAt->toDatabaseString(),
                    $this->status->value,
                    $this->validationCode,
                    $this->assignedCarrier?->getID(),
                    $this->resolutionNote,
                    $this->resolvedAt?->toDatabaseString(),
                    $this->resolvedBy?->getID()
                ]
            );
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE applications
                SET status = ?, validation_code = ?, assigned_carrier_id = ?, resolution_note = ?, resolved_at = ?, resolved_by_user_id = ?
                WHERE id = ?",
                [
                    $this->status->value,
                    $this->validationCode,
                    $this->assignedCarrier?->getID(),
                    $this->resolutionNote,
                    $this->resolvedAt?->toDatabaseString(),
                    $this->resolvedBy?->getID(),
                    $this->id
                ]
            );
            $this->wasModified = false;
        }
    }

    private function resolve(ApplicationStatus $targetStatus, ?Carrier $assignedCarrier, ?string $resolutionNote, User $resolver): void {
        Logger::log(LogLevel::info, "Application with ID \"{$this->id}\" is being resolved by user with ID \"{$resolver->getID()}\".");

        if ($this->status != ApplicationStatus::sent) {
            Logger::log(LogLevel::warn, "Failed to resolve the application.");
            throw new DomainException("Resolving an application with status other than \"SENT\" is not allowed.");
        }

        $this->status = $targetStatus;
        $this->assignedCarrier = $assignedCarrier;
        $this->resolutionNote = $resolutionNote;
        $this->resolvedAt = SystemDateTime::now();
        $this->resolvedBy = $resolver;
        $this->wasModified = true;
        $this->save();
        Logger::log(LogLevel::info, "New application status: \"{$this->status->value}\".");
    }
}

?>