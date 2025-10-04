<?php

final class Log {
    public readonly SystemDateTime $dateTime;
    public readonly LogLevel $level;
    public readonly string $message;
    public readonly ?string $logID;

    public function __construct(SystemDateTime $dateTime, LogLevel $level, string $message, ?string $logID = null) {
        $this->dateTime = $dateTime;
        $this->level = $level;
        $this->message = $message;
        $this->logID = $logID;
    }

    public function toFormattedString($processName): string {
        $logIDString = is_null($this->logID) ? "" : "[{$this->logID}]";
        return "[".$this->dateTime->toDatabaseString()."][$processName][".strtoupper($this->level->name)."]$logIDString: ".$this->message.PHP_EOL;
    }
}

?>