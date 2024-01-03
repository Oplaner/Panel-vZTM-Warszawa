<?php

class SystemDateTime {
    private static $mysqlDateFormat = "Y-m-d H:i:s";
    private static $localTimeZone = "Europe/Warsaw";
    private DateTimeImmutable $dateTime; // Always stored in UTC.

    public function __construct(?string $dateTime = null) {
        $timeZone = new DateTimeZone("UTC");

        if ($dateTime === null) {
            $this->dateTime = new DateTimeImmutable("now", $timeZone);
        } else {
            $this->dateTime = DateTimeImmutable::createFromFormat(self::$mysqlDateFormat, $dateTime, $timeZone);
        }
    }

    public function toDatabaseString(): string {
        return $this->dateTime->format(self::$mysqlDateFormat);
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

    private function getLocalizedDateTime(): DateTimeImmutable {
        $timeZone = new DateTimeZone(self::$localTimeZone);
        return $this->dateTime->setTimezone($timeZone);
    }
}

?>