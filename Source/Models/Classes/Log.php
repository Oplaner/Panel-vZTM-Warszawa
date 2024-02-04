<?php

require_once __DIR__."/SystemDateTime.php";
require_once __DIR__."/../Enums/LogLevel.php";

final class Log {
    public readonly SystemDateTime $dateTime;
    public readonly LogLevel $level;
    public readonly string $message;

    public function __construct(SystemDateTime $dateTime, LogLevel $level, string $message) {
        $this->dateTime = $dateTime;
        $this->level = $level;
        $this->message = $message;
    }

    public function __toString(): string {
        return "[".$this->dateTime->toDatabaseString()."][".strtoupper($this->level->name)."]: ".$this->message.PHP_EOL;
    }
}

?>