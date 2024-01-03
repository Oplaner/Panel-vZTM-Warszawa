<?php

require("TestClass.php");
require("../Source/Models/Classes/SystemDateTime.php");
require("../Source/Models/Enums/SystemDateTimeFormat.php");

class SystemDateTimeTests extends TestClass {
    public static function getTestMethods(): array {
        return [
            "createSystemDateTimeWithCurrentTime",
            "createSystemDateTimeWithSpecifiedTime",
            "checkDatabaseStringPattern",
            "checkTimeZoneDayShiftInLocalizedString",
            "checkLocalizedStringFullFormatPattern",
            "add14DaysToDateTime",
            "subtract4Hours30MinutesFromDateTime",
            "checkDateIsBeforeNow",
            "checkSystemDateTimesAreEqual",
            "checkNowIsAfterDate"
        ];
    }

    public static function createSystemDateTimeWithCurrentTime(): bool|string {
        $now = new SystemDateTime();

        if (!is_a($now, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($now).".";
        } else {
            return true;
        }
    }

    public static function createSystemDateTimeWithSpecifiedTime(): bool|string {
        $myBirthday = "1998-02-10 18:00:00";
        $then = new SystemDateTime($myBirthday);
        $databaseString = $then->toDatabaseString();

        if (!is_a($then, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($then).".";
        } elseif ($databaseString != $myBirthday) {
            return "Expected database string to be \"$myBirthday\". Found: \"$databaseString\".";
        } else {
            return true;
        }
    }

    public static function checkDatabaseStringPattern(): bool|string {
        $now = new SystemDateTime();
        $databaseString = $now->toDatabaseString();
        $pattern = "/^\-?\d{4}\-[01]\d\-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/";

        if (!preg_match($pattern, $databaseString)) {
            return "Database string \"$databaseString\" does not match correct pattern: $pattern.";
        } else {
            return true;
        }
    }

    public static function checkTimeZoneDayShiftInLocalizedString(): bool|string {
        $lastDayOfYear = new SystemDateTime("2023-12-31 23:00:00");
        $localizedString = $lastDayOfYear->toLocalizedString(SystemDateTimeFormat::DateOnly);
        $expectedString = "01.01.2024";

        if ($localizedString != $expectedString) {
            return "Expected localized string for {$lastDayOfYear->toDatabaseString()} UTC to be \"$expectedString\". Found: \"$localizedString\".";
        } else {
            return true;
        }
    }

    public static function checkLocalizedStringFullFormatPattern(): bool|string {
        $now = new SystemDateTime();
        $localizedString = $now->toLocalizedString(SystemDateTimeFormat::DateAndTimeWithSeconds);
        $pattern = "/^[0-3]\d\.[01]\d\.\-?\d{4}, [0-2]\d:[0-5]\d:[0-5]\d$/";

        if (!preg_match($pattern, $localizedString)) {
            return "Localized string \"$localizedString\" does not match correct pattern: $pattern.";
        } else {
            return true;
        }
    }

    public static function add14DaysToDateTime(): bool|string {
        $dateTime = new SystemDateTime("2024-01-27 09:41:00");
        $plus14Days = $dateTime->add(14, 0, 0);
        $databaseString = $plus14Days->toDatabaseString();
        $expectedString = "2024-02-10 09:41:00";

        if ($databaseString != $expectedString) {
            return "Expected \"$expectedString\" when adding 14 days to {$dateTime->toDatabaseString()}. Found: \"$databaseString\".";
        } else {
            return true;
        }
    }

    public static function subtract4Hours30MinutesFromDateTime(): bool|string {
        $dateTime = new SystemDateTime("2024-01-01 03:15:00");
        $minus4Hours30Minutes = $dateTime->subtract(0, 4, 30);
        $databaseString = $minus4Hours30Minutes->toDatabaseString();
        $expectedString = "2023-12-31 22:45:00";

        if ($databaseString != $expectedString) {
            return "Expected \"$expectedString\" when subtracting 4 hours and 30 minutes from {$dateTime->toDatabaseString()}. Found: \"$databaseString\".";
        } else {
            return true;
        }
    }

    public static function checkDateIsBeforeNow(): bool|string {
        $myBirthday = new SystemDateTime("1998-02-10 18:00:00");
        $now = new SystemDateTime();
        $comparison = $myBirthday->isBefore($now);

        if (!$comparison) {
            return "Checking if \"{$myBirthday->toDatabaseString()}\" is before \"{$now->toDatabaseString()}\" should return true. Found: \"$comparison\".";
        } else {
            return true;
        }
    }

    public static function checkSystemDateTimesAreEqual(): bool|string {
        $myBirthday1 = new SystemDateTime("1998-02-10 18:00:00");
        $myBirthday2 = new SystemDateTime("1998-02-10 18:00:00");
        $comparison = $myBirthday1->isEqual($myBirthday2);

        if (!$comparison) {
            return "Checking if \"{$myBirthday1->toDatabaseString()}\" is equal \"{$myBirthday2->toDatabaseString()}\" should return true. Found: \"$comparison\".";
        } else {
            return true;
        }
    }

    public static function checkNowIsAfterDate(): bool|string {
        $myBirthday = new SystemDateTime("1998-02-10 18:00:00");
        $now = new SystemDateTime();
        $comparison = $now->isAfter($myBirthday);

        if (!$comparison) {
            return "Checking if \"{$now->toDatabaseString()}\" is after \"{$myBirthday->toDatabaseString()}\" should return true. Found: \"$comparison\".";
        } else {
            return true;
        }
    }
}

?>