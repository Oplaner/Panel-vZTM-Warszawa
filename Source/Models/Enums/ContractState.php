<?php

enum ContractState: string {
    case active = "ACTIVE";
    case probation = "PROBATION";
    case probationWithPenalty = "PROBATION_WITH_PENALTY";
    case terminated = "TERMINATED";
    case terminatedAutomatically = "TERMINATED_AUTOMATICALLY";
    case terminatedDisciplinarily = "TERMINATED_DISCIPLINARILY";

    private const FINAL_STATES = [
        self::terminated,
        self::terminatedAutomatically,
        self::terminatedDisciplinarily
    ];

    public function isFinal(): bool {
        return in_array($this, self::FINAL_STATES);
    }
}

?>