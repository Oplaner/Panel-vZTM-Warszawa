<?php

final class SystemDateTime {
    private const MYSQL_DATETIME_FORMAT = "Y-m-d H:i:s.u";
    private const LOG_DATETIME_FORMAT = "Y-m-d_H-i-s";
    private const LOCAL_TIME_ZONE = "Europe/Warsaw";

    private DateTimeImmutable $dateTime; // Always stored in UTC.

    public function __construct(?string $dateTime = null) {
        $timeZone = new DateTimeZone("UTC");

        if (!is_null($dateTime)) {
            if (($dateTimeFromMySQLFormat = DateTimeImmutable::createFromFormat(self::MYSQL_DATETIME_FORMAT, $dateTime, $timeZone)) !== false) {
                $this->dateTime = $dateTimeFromMySQLFormat;
                return;
            }

            if (($dateTimeFromLogFormat = DateTimeImmutable::createFromFormat(self::LOG_DATETIME_FORMAT, $dateTime, $timeZone)) !== false) {
                $this->dateTime = $dateTimeFromLogFormat;
                return;
            }
        }

        $this->dateTime = new DateTimeImmutable("now", $timeZone);
    }

    public static function now(): SystemDateTime {
        return new SystemDateTime();
    }

    private static function prepareDateIntervalForCalculation(int $days, int $hours, int $minutes, int $seconds): DateInterval {
        $days = max(0, $days);
        $hours = max(0, $hours);
        $minutes = max(0, $minutes);
        $seconds = max(0, $seconds);
        return new DateInterval("P{$days}DT{$hours}H{$minutes}M{$seconds}S");
    }

    private static function createFromDateTimeImmutable(DateTimeImmutable $dateTime): SystemDateTime {
        $systemDateTime = new SystemDateTime();
        $systemDateTime->dateTime = $dateTime;
        return $systemDateTime;
    }

    public function toDatabaseString(): string {
        return $this->dateTime->format(self::MYSQL_DATETIME_FORMAT);
    }

    public function toLogString(): string {
        return $this->dateTime->format(self::LOG_DATETIME_FORMAT);
    }

    public function toLocalizedString(SystemDateTimeFormat $format): string {
        return $this->getLocalizedDateTime()->format($format->value);
    }

    public function adding(int $days, int $hours, int $minutes, int $seconds = 0): SystemDateTime {
        $dateInterval = self::prepareDateIntervalForCalculation($days, $hours, $minutes, $seconds);
        return self::createFromDateTimeImmutable($this->dateTime->add($dateInterval));
    }

    public function subtracting(int $days, int $hours, int $minutes, int $seconds = 0): SystemDateTime {
        $dateInterval = self::prepareDateIntervalForCalculation($days, $hours, $minutes, $seconds);
        return self::createFromDateTimeImmutable($this->dateTime->sub($dateInterval));
    }

    public function isBefore(SystemDateTime $other): bool {
        return $this->dateTime < $other->dateTime;
    }

    public function isEqual(SystemDateTime $other): bool {
        return $this->dateTime == $other->dateTime;
    }

    public function isAfter(SystemDateTime $other): bool {
        return $this->dateTime > $other->dateTime;
    }

    private function getLocalizedDateTime(): DateTimeImmutable {
        $timeZone = new DateTimeZone(self::LOCAL_TIME_ZONE);
        return $this->dateTime->setTimezone($timeZone);
    }
}

?>