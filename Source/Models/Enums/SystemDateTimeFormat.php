<?php

enum SystemDateTimeFormat: string {
    case dateAndTimeWithoutSeconds = self::date->value.", ".self::timeWithoutSeconds->value;
    case dateAndTimeWithSeconds = self::date->value.", ".self::timeWithSeconds->value;
    case date = "d.m.Y";
    case timeWithoutSeconds = "H:i";
    case timeWithSeconds = "H:i:s";
    case year = "Y";
}

?>