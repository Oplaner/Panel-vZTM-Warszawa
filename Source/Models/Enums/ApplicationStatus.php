<?php

enum ApplicationStatus: string {
    case approved = "APPROVED";
    case created = "CREATED";
    case expired = "EXPIRED";
    case rejected = "REJECTED";
    case sent = "SENT";
}

?>