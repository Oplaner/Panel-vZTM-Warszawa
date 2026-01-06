<?php

final class Application extends DatabaseEntity {
    private int $login;
    private string $username;
    private SystemDateTime $dateOfBirth;
    private string $passedExamProofURL;
    private string $motivation;
    private SystemDateTime $createdAt;
    private ApplicationStatus $status;
    private ?int $validationCode;
    private ?Carrier $assignedCarrier;
    private ?string $resolutionNote;
    private ?SystemDateTime $resolvedAt;
    private ?User $resolvedBy;

    private function __construct(?string $id, int $login, string $username, SystemDateTime $dateOfBirth, string $passedExamProofURL, string $motivation, SystemDateTime $createdAt, ApplicationStatus $status, ?int $validationCode, ?Carrier $assignedCarrier, ?string $resolutionNote, ?SystemDateTime $resolvedAt, ?User $resolvedBy) {
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
}

?>