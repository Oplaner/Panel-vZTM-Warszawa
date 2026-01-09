<?php

final class SystemDateTimeTests {
    public static function createSystemDateTimeWithCurrentTimeUsingConstructor(): bool|string {
        $now = new SystemDateTime();

        if (!is_a($now, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($now).".";
        }

        return true;
    }

    public static function createSystemDateTimeWithCurrentTimeUsingNowFunction(): bool|string {
        $now = SystemDateTime::now();

        if (!is_a($now, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($now).".";
        }

        return true;
    }

    public static function createSystemDateTimeWithSpecifiedTimeUsingMySQLDateFormat(): bool|string {
        $myBirthday = "1998-02-10";
        $then = new SystemDateTime($myBirthday);
        $databaseString = $then->toDatabaseString(true);

        if (!is_a($then, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($then).".";
        } elseif ($databaseString != $myBirthday) {
            return "Expected database string to be \"$myBirthday\". Found: \"$databaseString\".";
        }

        return true;
    }

    public static function createSystemDateTimeWithSpecifiedTimeUsingMySQLDateTimeFormat(): bool|string {
        $myBirthday = "1998-02-10 18:00:00.000000";
        $then = new SystemDateTime($myBirthday);
        $databaseString = $then->toDatabaseString();

        if (!is_a($then, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($then).".";
        } elseif ($databaseString != $myBirthday) {
            return "Expected database string to be \"$myBirthday\". Found: \"$databaseString\".";
        }

        return true;
    }

    public static function createSystemDateTimeWithSpecifiedTimeUsingLogFormat(): bool|string {
        $myBirthday = "1998-02-10_18-00-00";
        $then = new SystemDateTime($myBirthday);
        $logString = $then->toLogString();

        if (!is_a($then, SystemDateTime::class)) {
            return "Expected a ".SystemDateTime::class." object. Found: ".gettype($then).".";
        } elseif ($logString != $myBirthday) {
            return "Expected log string to be \"$myBirthday\". Found: \"$logString\".";
        }

        return true;
    }

    public static function checkTimestamp(): bool|string {
        $myBirthday = new SystemDateTime("1998-02-10 18:00:00.000000");
        $expectedTimestamp = 887133600;

        if ($myBirthday->toTimestamp() != $expectedTimestamp) {
            return "Expected timestamp for {$myBirthday->toDatabaseString()} UTC to be $expectedTimestamp. Found: {$myBirthday->toTimestamp()}.";
        }

        return true;
    }

    public static function checkDatabaseStringPattern(): bool|string {
        $now = new SystemDateTime();
        $databaseString = $now->toDatabaseString();
        $pattern = "/^\-?\d+\-[01]\d\-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d\.\d{6}$/";

        if (!preg_match($pattern, $databaseString)) {
            return "Database string \"$databaseString\" does not match correct pattern: $pattern.";
        }

        return true;
    }

    public static function checkLogStringPattern(): bool|string {
        $now = new SystemDateTime();
        $logString = $now->toLogString();
        $pattern = "/^\-?\d+\-[01]\d\-[0-3]\d_[0-2]\d\-[0-5]\d\-[0-5]\d$/";

        if (!preg_match($pattern, $logString)) {
            return "Log string \"$logString\" does not match correct pattern: $pattern.";
        }

        return true;
    }

    public static function checkTimeZoneDayShiftInLocalizedString(): bool|string {
        $lastDayOfYear = new SystemDateTime("2023-12-31 23:00:00.000000");
        $localizedString = $lastDayOfYear->toLocalizedString(SystemDateTimeFormat::dateOnly);
        $expectedString = "01.01.2024";

        if ($localizedString != $expectedString) {
            return "Expected localized string for {$lastDayOfYear->toDatabaseString()} UTC to be \"$expectedString\". Found: \"$localizedString\".";
        }

        return true;
    }

    public static function checkLocalizedStringFullFormatPattern(): bool|string {
        $now = new SystemDateTime();
        $localizedString = $now->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithSeconds);
        $pattern = "/^[0-3]\d\.[01]\d\.\-?\d+, [0-2]\d:[0-5]\d:[0-5]\d$/";

        if (!preg_match($pattern, $localizedString)) {
            return "Localized string \"$localizedString\" does not match correct pattern: $pattern.";
        }

        return true;
    }

    public static function add14Days15SecondsToDateTime(): bool|string {
        $dateTime = new SystemDateTime("2024-01-27 09:41:00.000000");
        $plus14Days = $dateTime->adding(14, 0, 0, 15);
        $databaseString = $plus14Days->toDatabaseString();
        $expectedString = "2024-02-10 09:41:15.000000";

        if ($databaseString != $expectedString) {
            return "Expected \"$expectedString\" when adding 14 days and 15 seconds to {$dateTime->toDatabaseString()}. Found: \"$databaseString\".";
        }

        return true;
    }

    public static function subtract4Hours30MinutesFromDateTime(): bool|string {
        $dateTime = new SystemDateTime("2024-01-01 03:15:00.000000");
        $minus4Hours30Minutes = $dateTime->subtracting(0, 4, 30);
        $databaseString = $minus4Hours30Minutes->toDatabaseString();
        $expectedString = "2023-12-31 22:45:00.000000";

        if ($databaseString != $expectedString) {
            return "Expected \"$expectedString\" when subtracting 4 hours and 30 minutes from {$dateTime->toDatabaseString()}. Found: \"$databaseString\".";
        }

        return true;
    }

    public static function checkDateIsBeforeNow(): bool|string {
        $myBirthday = new SystemDateTime("1998-02-10 18:00:00.000000");
        $now = new SystemDateTime();
        $comparison = $myBirthday->isBefore($now);

        if ($comparison == false) {
            return "Checking if \"{$myBirthday->toDatabaseString()}\" is before \"{$now->toDatabaseString()}\" should return true. Found: \"$comparison\".";
        }

        return true;
    }

    public static function checkSystemDateTimesAreEqual(): bool|string {
        $myBirthday1 = new SystemDateTime("1998-02-10 18:00:00.000000");
        $myBirthday2 = new SystemDateTime("1998-02-10 18:00:00.000000");
        $comparison = $myBirthday1->isEqual($myBirthday2);

        if ($comparison == false) {
            return "Checking if \"{$myBirthday1->toDatabaseString()}\" is equal \"{$myBirthday2->toDatabaseString()}\" should return true. Found: \"$comparison\".";
        }

        return true;
    }

    public static function checkNowIsAfterDate(): bool|string {
        $myBirthday = new SystemDateTime("1998-02-10 18:00:00.000000");
        $now = new SystemDateTime();
        $comparison = $now->isAfter($myBirthday);

        if ($comparison == false) {
            return "Checking if \"{$now->toDatabaseString()}\" is after \"{$myBirthday->toDatabaseString()}\" should return true. Found: \"$comparison\".";
        }

        return true;
    }
}

?>