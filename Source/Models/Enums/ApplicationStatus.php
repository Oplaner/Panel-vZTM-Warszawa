<?php

enum ApplicationStatus: string {
    case created = "CREATED";
    case sent = "SENT";
    case expired = "EXPIRED";
    case rejected = "REJECTED";
    case approved = "APPROVED";
}

?>