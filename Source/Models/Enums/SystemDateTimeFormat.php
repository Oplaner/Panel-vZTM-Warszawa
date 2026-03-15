<?php

enum SystemDateTimeFormat: string {
    case dateAndTimeWithoutSeconds = self::dateOnly->value.", ".self::timeOnlyWithoutSeconds->value;
    case dateAndTimeWithSeconds = self::dateOnly->value.", ".self::timeOnlyWithSeconds->value;
    case dateOnly = "d.m.Y";
    case timeOnlyWithoutSeconds = "H:i";
    case timeOnlyWithSeconds = "H:i:s";
}

?>