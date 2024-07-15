<?php

final class Log {
    public readonly SystemDateTime $dateTime;
    public readonly LogLevel $level;
    public readonly string $message;

    public function __construct(SystemDateTime $dateTime, LogLevel $level, string $message) {
        $this->dateTime = $dateTime;
        $this->level = $level;
        $this->message = $message;
    }

    public function toFormattedString($processName): string {
        return "[".$this->dateTime->toDatabaseString()."][$processName][".strtoupper($this->level->name)."]: ".$this->message.PHP_EOL;
    }
}

?>