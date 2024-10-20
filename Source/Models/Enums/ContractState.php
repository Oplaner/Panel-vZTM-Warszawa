<?php

enum ContractState: string {
    case conditional = "CONDITIONAL";
    case conditionalWithPenalty = "CONDITIONAL_WITH_PENALTY";
    case regular = "REGULAR";
    case terminated = "TERMINATED";
    case terminatedDisciplinarily = "TERMINATED_DISCIPLINARILY";
}

?>