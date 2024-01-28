<?php

require_once __DIR__."/Log.php";
require_once __DIR__."/PropertiesReader.php";
require_once __DIR__."/SystemDateTime.php";
require_once __DIR__."/../Enums/LogLevel.php";

final class Logger {
    private const EXCLUDED_LOG_DIRECTORY_ELEMENTS = [".", ".."];
    private const LOG_FILE_PATTERN = "/^\-?\d+\-[01]\d\-[0-3]\d_[0-2]\d\-[0-5]\d\-[0-5]\d\.log$/";
    private static ?Logger $sharedInstance = null;
    private array $logs = [];

    private function __construct(Log $initialLog) {
        $this->logs[] = $initialLog;
    }

    public static function log(LogLevel $level, String $message): void {
        $log = new Log(SystemDateTime::now(), $level, $message);

        if (is_null(self::$sharedInstance)) {
            self::$sharedInstance = new Logger($log);
        } else {
            $properties = PropertiesReader::getProperties("logger");
            self::$sharedInstance->logs[] = $log;

            if (count(self::$sharedInstance->logs) == $properties["maxBatchSize"]) {
                self::$sharedInstance->write();
            }
        }
    }

    private static function makePathForLogFile(string $logFileName): string {
        $properties = PropertiesReader::getProperties("logger");
        return __DIR__.$properties["directory"].$logFileName.".log";
    }

    private static function compareLogFileNames(string $a, string $b, bool $ascending = true): int {
        $direction = $ascending ? 1 : -1;
        $dateTimeA = new SystemDateTime($a);
        $dateTimeB = new SystemDateTime($b);

        if ($dateTimeA->isBefore($dateTimeB)) {
            return -1 * $direction;
        } elseif ($dateTimeA->isAfter($dateTimeB)) {
            return 1 * $direction;
        }

        return 0;
    }

    private static function findAllLogFileNames(): array {
        $properties = PropertiesReader::getProperties("logger");
        return array_map(
            fn ($file) => preg_replace("/^(\S+)\.log$/", "$1", $file),
            array_filter(
                array_diff(
                    scandir(__DIR__.$properties["directory"]),
                    self::EXCLUDED_LOG_DIRECTORY_ELEMENTS
                ),
                fn ($file) => preg_match(self::LOG_FILE_PATTERN, $file)
            )
        );
    }

    private static function findLatestLogFileName(): ?string {
        $logFileNames = self::findAllLogFileNames();
        usort($logFileNames, fn ($a, $b) => self::compareLogFileNames($a, $b, false));
        return count($logFileNames) == 0 ? null : $logFileNames[0];
    }

    private static function removeExpiredLogFiles(): void {
        $properties = PropertiesReader::getProperties("logger");
        $logFileNames = self::findAllLogFileNames();
        usort($logFileNames, fn ($a, $b) => self::compareLogFileNames($a, $b));

        foreach ($logFileNames as $logFileName) {
            $logFileDateTime = new SystemDateTime($logFileName);
            $logFileDateTimePlusRetention = $logFileDateTime->adding(
                $properties["retentionDays"],
                $properties["retentionHours"],
                $properties["retentionMinutes"],
            );

            if ($logFileDateTimePlusRetention->isBefore(SystemDateTime::now())) {
                $path = self::makePathForLogFile($logFileName);
                unlink($path);
            } else {
                break;
            }
        }
    }

    private static function calculateLogFileEndDateTime(SystemDateTime $logFileStartDateTime): SystemDateTime {
        $properties = PropertiesReader::getProperties("logger");
        return $logFileStartDateTime->adding(
            $properties["maxPeriodDays"],
            $properties["maxPeriodHours"],
            $properties["maxPeriodMinutes"]
        );
    }

    private function flushLog(&$file, int $index): void {
        fwrite($file, $this->logs[$index]);
        unset($this->logs[$index]);
    }

    private function closeFileIfNeeded(&$file): void {
        if (is_resource($file)) {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    private function write(): void {
        self::removeExpiredLogFiles();

        if (count($this->logs) == 0) {
            return;
        }

        $currentLogFileName = self::findLatestLogFileName();
        $currentLogFileEndDateTime = null;

        if (!is_null($currentLogFileName)) {
            $currentLogFileStartDateTime = new SystemDateTime($currentLogFileName);
            $currentLogFileEndDateTime = self::calculateLogFileEndDateTime($currentLogFileStartDateTime);
        }

        $this->logs = array_reverse($this->logs); // For safe element removal.
        $file = null;

        while (count($this->logs) > 0) {
            $i = count($this->logs) - 1;

            if (is_null($currentLogFileName)) {
                $currentLogFileName = $this->logs[$i]->dateTime->toLogString();
                $currentLogFileEndDateTime = self::calculateLogFileEndDateTime($this->logs[$i]->dateTime);
            }

            if ($this->logs[$i]->dateTime->isBefore($currentLogFileEndDateTime)) {
                if (is_resource($file)) {
                    $this->flushLog($file, $i);
                } else {
                    $path = self::makePathForLogFile($currentLogFileName);

                    if (file_exists($path)) {
                        if (($file = fopen($path, "a")) !== false && flock($file, LOCK_EX)) {
                            $this->flushLog($file, $i);
                        } else {
                            $this->closeFileIfNeeded($file);
                        }
                    } else {
                        if (($file = fopen($path, "w")) !== false && flock($file, LOCK_EX)) {
                            $this->flushLog($file, $i);
                        } else {
                            $this->closeFileIfNeeded($file);
                        }
                    }
                }
            } else {
                $currentLogFileName = null;
                $currentLogFileEndDateTime = null;
                $this->closeFileIfNeeded($file);
            }
        }

        $this->closeFileIfNeeded($file);
    }

    public function __destruct() {
        $this->write();
    }
}

?>