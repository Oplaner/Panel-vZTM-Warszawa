<?php

final class ContractPeriod extends DatabaseEntity {
    private string $contractID;
    private ContractState $state;
    private SystemDateTime $validFrom;
    private User $authorizedBy;
    private ?SystemDateTime $validTo;

    private function __construct(?string $id, string $contractID, ContractState $state, SystemDateTime $validFrom, User $authorizedBy, ?SystemDateTime $validTo) {
        parent::__construct($id);
        $this->contractID = $contractID;
        $this->state = $state;
        $this->validFrom = $validFrom;
        $this->authorizedBy = $authorizedBy;
        $this->validTo = $validTo;
        $this->save();
    }

    public static function createNew(Contract $contract, ContractState $state, SystemDateTime $validFrom, User $authorizedBy): ContractPeriod {
        Logger::log(LogLevel::info, "Creating new contract period of contract with ID \"{$contract->getID()}\", with state \"{$state->value}\" valid from {$validFrom->toDatabaseString()} and authorized by user with ID \"{$authorizedBy->getID()}\".");
        return new ContractPeriod(null, $contract->getID(), $state, $validFrom, $authorizedBy, null);
    }

    public static function withID(string $id): ?ContractPeriod {
        Logger::log(LogLevel::info, "Fetching contract period with ID \"$id\".");
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, ContractPeriod::class)) {
            Logger::log(LogLevel::info, "Found cached contract period: $cachedObject.");
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT contract_id, state, valid_from, authorized_by_user_id, valid_to
            FROM contract_periods
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find contract period with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $contractID = $data["contract_id"];
        $state = ContractState::from($data["state"]);
        $validFrom = new SystemDateTime($data["valid_from"]);
        $authorizedBy = User::withID($data["authorized_by_user_id"]);
        $validTo = is_null($data["valid_to"]) ? null : new SystemDateTime($data["valid_to"]);
        $contractPeriod = new ContractPeriod($id, $contractID, $state, $validFrom, $authorizedBy, $validTo);
        Logger::log(LogLevel::info, "Fetched contract period: $contractPeriod.");
        return $contractPeriod;
    }

    public static function getAllPeriodsOfContract(Contract $contract): array {
        Logger::log(LogLevel::info, "Fetching all periods of contract with ID \"{$contract->getID()}\".");
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT id
            FROM contract_periods
            WHERE contract_id = ?
            ORDER BY valid_from ASC",
            [
                $contract->getID()
            ]
        );
        $periods = [];

        while ($data = $result->fetch_assoc()) {
            $periodID = $data["id"];
            $periods[] = self::withID($periodID);
        }

        $result->free();
        Logger::log(LogLevel::info, "Found ".count($periods)." period(s) for contract with ID \"{$contract->getID()}\".");
        return $periods;
    }

    public function getState(): ContractState {
        return $this->state;
    }

    public function getValidFrom(): SystemDateTime {
        return $this->validFrom;
    }

    public function getAuthorizedBy(): User {
        return $this->authorizedBy;
    }

    public function getValidTo(): ?SystemDateTime {
        return $this->validTo;
    }

    public function setValidTo(SystemDateTime $validTo): void {
        Logger::log(LogLevel::info, "Contract period with ID \"{$this->getID()}\" is having its validTo value changed to {$validTo->toDatabaseString()}.");
        $this->validTo = $validTo;
        $this->wasModified = true;
        $this->save();
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", contractID: \"%s\", state: \"%s\", validFrom: %s, authorizedByUserID: \"%s\", validTo: %s, isNew: %s, wasModified: %s)",
            $this->id,
            $this->contractID,
            $this->state->value,
            $this->validFrom->toDatabaseString(),
            $this->authorizedBy->getID(),
            is_null($this->validTo) ? "null" : $this->validTo->toDatabaseString(),
            $this->isNew ? "true" : "false",
            $this->wasModified ? "true" : "false"
        );
    }

    protected function save(): void {
        Logger::log(LogLevel::info, "Saving ".($this->isNew ? "new" : "existing")." contract period: $this.");
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $db->execute_query(
                "INSERT INTO contract_periods
                (id, contract_id, state, valid_from, authorized_by_user_id, valid_to)
                VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->contractID,
                    $this->state->value,
                    $this->validFrom->toDatabaseString(),
                    $this->authorizedBy->getID(),
                    is_null($this->validTo) ? null : $this->validTo->toDatabaseString()
                ]
            );
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE contract_periods
                SET valid_to = ?
                WHERE id = ?",
                [
                    $this->validTo->toDatabaseString(),
                    $this->id
                ]
            );
            $this->wasModified = false;
        }
    }
}

?>