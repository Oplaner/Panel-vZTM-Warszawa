<?php

enum ContractState: string {
    case probation = "PROBATION";
    case probationWithPenalty = "PROBATION_WITH_PENALTY";
    case active = "ACTIVE";
    case terminated = "TERMINATED";
    case terminatedDisciplinarily = "TERMINATED_DISCIPLINARILY";
    case terminatedAutomatically = "TERMINATED_AUTOMATICALLY";

    private const FINAL_STATES = [
        self::terminated,
        self::terminatedDisciplinarily,
        self::terminatedAutomatically
    ];

    public function isFinal(): bool {
        return in_array($this, self::FINAL_STATES);
    }
}

?>