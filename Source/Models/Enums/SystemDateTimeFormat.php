<?php

enum SystemDateTimeFormat: string {
    case dateOnly = "d.m.Y";
    case timeOnlyWithoutSeconds = "H:i";
    case timeOnlyWithSeconds = "H:i:s";
    case dateAndTimeWithoutSeconds = self::dateOnly->value.", ".self::timeOnlyWithoutSeconds->value;
    case dateAndTimeWithSeconds = self::dateOnly->value.", ".self::timeOnlyWithSeconds->value;
}

?>