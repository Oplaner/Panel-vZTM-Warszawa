<?php

final class SystemDateTime {
    private const MYSQL_DATE_FORMAT = "Y-m-d H:i:s";
    private const LOCAL_TIME_ZONE = "Europe/Warsaw";
    private DateTimeImmutable $dateTime; // Always stored in UTC.

    public function __construct(?string $dateTime = null) {
        $timeZone = new DateTimeZone("UTC");

        if (is_null($dateTime)) {
            $this->dateTime = new DateTimeImmutable("now", $timeZone);
        } else {
            $this->dateTime = DateTimeImmutable::createFromFormat(self::MYSQL_DATE_FORMAT, $dateTime, $timeZone);
        }
    }

    public static function now(): SystemDateTime {
        return new SystemDateTime();
    }

    private static function prepareDateIntervalForCalculation(int $days, int $hours, int $minutes): DateInterval {
        $days = max(0, $days);
        $hours = max(0, $hours);
        $minutes = max(0, $minutes);
        return new DateInterval("P{$days}DT{$hours}H{$minutes}M");
    }

    private static function createFromDateTimeImmutable(DateTimeImmutable $dateTime): SystemDateTime {
        $systemDateTime = new SystemDateTime();
        $systemDateTime->dateTime = $dateTime;
        return $systemDateTime;
    }

    public function toDatabaseString(): string {
        return $this->dateTime->format(self::MYSQL_DATE_FORMAT);
    }

    public function toLocalizedString(SystemDateTimeFormat $format): string {
        return $this->getLocalizedDateTime()->format($format->value);
    }

    public function add(int $days, int $hours, int $minutes): SystemDateTime {
        $dateInterval = self::prepareDateIntervalForCalculation($days, $hours, $minutes);
        return self::createFromDateTimeImmutable($this->dateTime->add($dateInterval));
    }

    public function subtract(int $days, int $hours, int $minutes): SystemDateTime {
        $dateInterval = self::prepareDateIntervalForCalculation($days, $hours, $minutes);
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