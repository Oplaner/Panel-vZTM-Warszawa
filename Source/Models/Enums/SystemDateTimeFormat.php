<?php

enum SystemDateTimeFormat: string {
    case DateOnly = "d.m.Y";
    case TimeOnlyWithoutSeconds = "H:i";
    case TimeOnlyWithSeconds = "H:i:s";
    case DateAndTimeWithoutSeconds = self::DateOnly->value.", ".self::TimeOnlyWithoutSeconds->value;
    case DateAndTimeWithSeconds = self::DateOnly->value.", ".self::TimeOnlyWithSeconds->value;
}

?>