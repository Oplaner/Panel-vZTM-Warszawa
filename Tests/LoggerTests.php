<?php

require_once "../Source/Models/Classes/Logger.php";
require_once "../Source/Models/Classes/PropertiesReader.php";
require_once "../Source/Models/Enums/LogLevel.php";

final class LoggerTests {
    private const LOG_FILES_DIRECTORY = "../Logs/";
    private const LOG_FILE_PATTERN = "/^\-?\d+\-[01]\d\-[0-3]\d_[0-2]\d\-[0-5]\d\-[0-5]\d\.log$/";

    public static function checkLogsDirectorySizeIncreasesAfterLoggingMessages(): bool|string {
        self::logMessagesUntilWrite();
        $sizeBeforeLogging = self::getLogsDirectorySize();
        self::logMessagesUntilWrite();
        $sizeAfterLogging = self::getLogsDirectorySize();

        if ($sizeBeforeLogging >= $sizeAfterLogging) {
            return "Logs directory size did not increase after logging a message. Size before: $sizeBeforeLogging B, after: $sizeAfterLogging B.";
        }

        return true;
    }

    public static function checkNewMessagesAreAppendedToLatestFileWhenItsPeriodIsNotFinished(): bool|string {
        self::logMessagesUntilWrite();
        self::hideLogFiles(true);

        $properties = PropertiesReader::getProperties("logger");
        $logFileName = SystemDateTime::now()
            ->subtracting(
                $properties["maxPeriodDays"],
                $properties["maxPeriodHours"],
                $properties["maxPeriodMinutes"]
            )
            ->adding(0, 0, 1)
            ->toLogString().".log";
        $path = self::LOG_FILES_DIRECTORY.$logFileName;

        if (file_put_contents($path, "") === false) {
            return "Failed to create log file with unfinished period at \"$path\".";
        }

        $countBeforeLogging = count(self::getLogFiles());
        self::logMessagesUntilWrite();
        $countAfterLogging = count(self::getLogFiles());

        self::deleteLogFiles();
        self::hideLogFiles(false);

        if ($countBeforeLogging != $countAfterLogging) {
            return "The number of log files changed. Count before: $countBeforeLogging, after: $countAfterLogging.";
        }

        return true;
    }

    public static function checkNewMessagesAreWrittenToNewFileWhenLatestFilePeriodIsFinished(): bool|string {
        self::logMessagesUntilWrite();
        self::hideLogFiles(true);

        $properties = PropertiesReader::getProperties("logger");
        $logFileName = SystemDateTime::now()
            ->subtracting(
                $properties["maxPeriodDays"],
                $properties["maxPeriodHours"],
                $properties["maxPeriodMinutes"]
            )
            ->toLogString().".log";
        $path = self::LOG_FILES_DIRECTORY.$logFileName;

        if (file_put_contents($path, "") === false) {
            return "Failed to create log file with finished period at \"$path\".";
        }

        $countBeforeLogging = count(self::getLogFiles());
        self::logMessagesUntilWrite();
        $countAfterLogging = count(self::getLogFiles());

        self::deleteLogFiles();
        self::hideLogFiles(false);

        if ($countBeforeLogging >= $countAfterLogging) {
            return "Current number of log files is incorrect. Count before: $countBeforeLogging, after: $countAfterLogging.";
        }

        return true;
    }

    public static function checkExpiredLogFilesAreDeletedDuringWrite(): bool|string {
        $properties = PropertiesReader::getProperties("logger");
        $expiredLogFileName = SystemDateTime::now()
            ->subtracting(
                $properties["retentionDays"],
                $properties["retentionHours"],
                $properties["retentionMinutes"]
            )
            ->toLogString().".log";
        $path = self::LOG_FILES_DIRECTORY.$expiredLogFileName;

        if (file_put_contents($path, "") === false) {
            return "Failed to create expired log file at \"$path\".";
        }

        self::logMessagesUntilWrite();

        if (file_exists($path)) {
            return "Expired log file \"$expiredLogFileName\" was not deleted during write.";
        }

        return true;
    }

    private static function getLogFiles(bool $hidden = false): array {
        $pattern = $hidden ? str_replace("log", "hiddenlog", self::LOG_FILE_PATTERN) : self::LOG_FILE_PATTERN;
        return array_filter(
            array_diff(
                scandir(self::LOG_FILES_DIRECTORY),
                [".", ".."]
            ),
            fn ($file) => preg_match($pattern, $file)
        );
    }

    private static function getLogsDirectorySize(): int {
        clearstatcache();
        return array_reduce(
            array_map(
                fn ($file) => filesize(self::LOG_FILES_DIRECTORY.$file),
                self::getLogFiles()
            ),
            fn ($carry, $item) => $carry + $item,
            0
        );
    }

    private static function logMessagesUntilWrite(): void {
        $initialLogsDirectorySize = self::getLogsDirectorySize();
        $currentLogsDirectorySize = $initialLogsDirectorySize;

        do {
            Logger::log(LogLevel::info, __CLASS__." sample message.");
            $currentLogsDirectorySize = self::getLogsDirectorySize();
        } while ($initialLogsDirectorySize == $currentLogsDirectorySize);
    }

    private static function hideLogFiles(bool $hide): void {
        foreach (self::getLogFiles(!$hide) as $file) {
            $old = self::LOG_FILES_DIRECTORY.$file;
            $pattern = $hide ? "/^(.+)\.log$/" : "/^(.+)\.hiddenlog$/";
            $replacement = $hide ? "$1.hiddenlog" : "$1.log";
            $new = preg_replace($pattern, $replacement, $old);
            rename($old, $new);
        }
    }

    private static function deleteLogFiles(): void {
        foreach (self::getLogFiles() as $file) {
            unlink(self::LOG_FILES_DIRECTORY.$file);
        }
    }
}

?>