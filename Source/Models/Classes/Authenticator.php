<?php

require_once __DIR__."/DatabaseEntity.php";
require_once __DIR__."/PropertiesReader.php";
require_once __DIR__."/Logger.php";
require_once __DIR__."/SystemDateTime.php";
require_once __DIR__."/User.php";
require_once __DIR__."/../Enums/AuthenticationResult.php";
require_once __DIR__."/../Enums/LogLevel.php";

final class Authenticator {
    private const SESSION_COOKIE_HTTPONLY = true;
    private const SESSION_COOKIE_SAMESITE = "Lax";
    private const TEMPORARY_PASSWORD_CHARACTER_GROUPS = [
        "uppercaseLetters" => "ABCDEFGHJKLMNPQRTUVWXY",
        "lowercaseLetters" => "abcdefghjkmnpqrstuvwxyz",
        "digits" => "346789",
        "specialCharacters" => "!@#$%&?"
    ];

    public static function getUserFromSessionData(): ?User {
        $_USER = null;
        $properties = PropertiesReader::getProperties("authenticator");
        self::deleteExpiredSessionTokens();
        
        if (isset($_COOKIE[$properties["sessionName"]])) {
            self::startSessionWithOptions();

            if (isset($_SESSION["token"])) {
                $sessionID = session_id();
                $token = $_SESSION["token"];
                $agentHash = self::makeAgentHash();
                Logger::log(LogLevel::info, "Found session token. Trying to get user ID for session with ID: \"$sessionID\", token: \"$token\", agent hash: \"$agentHash\".");

                $db = DatabaseConnector::shared();
                $result = $db->execute_query(
                    "SELECT user_id, session_id_refreshed_at
                    FROM session_tokens
                    WHERE token = ? AND session_id = ? AND agent_hash = ? AND valid_to > ?",
                    [
                        $token,
                        $sessionID,
                        $agentHash,
                        SystemDateTime::now()->toDatabaseString()
                    ]
                );

                if ($result->num_rows == 0) {
                    $result->free();
                    Logger::log(LogLevel::info, "Session data is invalid. Continuing without a user.");
                    self::endSession();
                } else {
                    $data = $result->fetch_assoc();
                    $result->free();
                    $userID = $data["user_id"];
                    Logger::log(LogLevel::info, "Session data is valid for user with ID \"$userID\".");
                    $lastRefresh = new SystemDateTime($data["session_id_refreshed_at"]);
                    $shouldRefresh = $lastRefresh
                        ->adding(0, 0, 0, $properties["sessionIDRefreshIntervalSeconds"])
                        ->isBefore(SystemDateTime::now());

                    if ($shouldRefresh) {
                        session_regenerate_id(true);
                        Logger::log(LogLevel::info, "Refreshing session ID. Old: \"$sessionID\", new: \"".session_id()."\".");
                        $lastRefresh = SystemDateTime::now();
                    }

                    self::extendSession($lastRefresh);
                    $_USER = User::withID($userID);
                }
            } else {
                Logger::log(LogLevel::info, "Session token was not found. Continuing without a user.");
                self::endSession();
            }
        } else {
            Logger::log(LogLevel::info, "Session cookie was not found. Continuing without a user.");
        }

        return $_USER;
    }

    public static function authenticateUser(string $login, string $password): AuthenticationResult {
        global $_USER;

        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT id, password, password_valid_to
            FROM users
            WHERE login = ?",
            [
                (int) $login,
            ]
        );

        if ($result->num_rows == 1) {
            $data = $result->fetch_assoc();
            $result->free();
            $userID = $data["id"];
            $passwordHash = $data["password"];
            $passwordValidTo = $data["password_valid_to"];

            if (password_verify($password, $passwordHash)) {
                if (is_null($passwordValidTo)
                || (new SystemDateTime($passwordValidTo))->isAfter(SystemDateTime::now())) {
                    Logger::log(LogLevel::info, "Successfully authenticated user with ID \"$userID\".");
                    self::startUserSession($userID);
                    $_USER = User::withID($userID);
                    return AuthenticationResult::success;
                } else {
                    Logger::log(LogLevel::info, "Failed to authenticate user with login \"$login\". Expired password.");
                    return AuthenticationResult::expiredPassword;
                }
            } else {
                Logger::log(LogLevel::info, "Failed to authenticate user with login \"$login\". Incorrect password.");
            }
        } else {
            $result->free();
            Logger::log(LogLevel::info, "Failed to authenticate user with login \"$login\". Login not found.");
        }

        return AuthenticationResult::invalidCredentials;
    }

    public static function endUserSession(): void {
        global $_USER;
        $token = $_SESSION["token"];
        Logger::log(LogLevel::info, "Ending session of user with ID \"{$_USER->getID()}\".");

        DatabaseConnector::shared()->execute_query(
            "DELETE FROM session_tokens
            WHERE token = ?",
            [
                $token
            ]
        );

        Logger::log(LogLevel::info, "Deleted session token \"$token\" from the database.");
        self::endSession();
        $_USER = null;
    }

    public static function generateTemporaryPassword(): string {
        $properties = PropertiesReader::getProperties("authenticator");
        $minPasswordLength = $properties["minPasswordLength"];
        $minNumberOfUppercaseLetters = $properties["minNumberOfUppercaseLetters"];
        $minNumberOfLowercaseLetters = $properties["minNumberOfLowercaseLetters"];
        $minNumberOfDigits = $properties["minNumberOfDigits"];
        $minNumberOfSpecialCharacters = $properties["minNumberOfSpecialCharacters"];
        $uppercaseLetters = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["uppercaseLetters"]);
        $lowercaseLetters = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["lowercaseLetters"]);
        $digits = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["digits"]);
        $specialCharacters = str_split(self::TEMPORARY_PASSWORD_CHARACTER_GROUPS["specialCharacters"]);
        $allCharacters = array_merge($uppercaseLetters, $lowercaseLetters, $digits, $specialCharacters);
        $passwordCharacterPool = [];

        for ($i = 0; $i < $minNumberOfUppercaseLetters; $i++) {
            $passwordCharacterPool[] = $uppercaseLetters[rand(0, count($uppercaseLetters) - 1)];
        }

        for ($i = 0; $i < $minNumberOfLowercaseLetters; $i++) {
            $passwordCharacterPool[] = $lowercaseLetters[rand(0, count($lowercaseLetters) - 1)];
        }

        for ($i = 0; $i < $minNumberOfDigits; $i++) {
            $passwordCharacterPool[] = $digits[rand(0, count($digits) - 1)];
        }

        for ($i = 0; $i < $minNumberOfSpecialCharacters; $i++) {
            $passwordCharacterPool[] = $specialCharacters[rand(0, count($specialCharacters) - 1)];
        }

        while (count($passwordCharacterPool) < $minPasswordLength) {
            $passwordCharacterPool[] = $allCharacters[rand(0, count($allCharacters) - 1)];
        }

        shuffle($passwordCharacterPool);
        return implode($passwordCharacterPool);
    }

    public static function passwordFulfillsRequirements(string $password): bool {
        $properties = PropertiesReader::getProperties("authenticator");
        $minPasswordLength = $properties["minPasswordLength"];
        $minNumberOfUppercaseLetters = $properties["minNumberOfUppercaseLetters"];
        $minNumberOfLowercaseLetters = $properties["minNumberOfLowercaseLetters"];
        $minNumberOfDigits = $properties["minNumberOfDigits"];
        $minNumberOfSpecialCharacters = $properties["minNumberOfSpecialCharacters"];

        if (mb_strlen($password, "UTF-8") < $minPasswordLength
        || preg_match_all("/\p{Lu}/u", $password) < $minNumberOfUppercaseLetters
        || preg_match_all("/\p{Ll}/u", $password) < $minNumberOfLowercaseLetters
        || preg_match_all("/\d/", $password) < $minNumberOfDigits
        || preg_match_all("/[^\p{Lu}\p{Ll}\d\s]/u", $password) < $minNumberOfSpecialCharacters) {
            return false;
        }

        return true;
    }

    private static function deleteExpiredSessionTokens(): void {
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "DELETE FROM session_tokens
            WHERE valid_to <= ?",
            [
                SystemDateTime::now()->toDatabaseString()
            ]
        );
        Logger::log(LogLevel::info, "Deleted expired session tokens ({$db->affected_rows}).");
    }

    private static function startUserSession(string $userID): void {
        $properties = PropertiesReader::getProperties("authenticator");
        self::startSessionWithOptions();
        $token = DatabaseEntity::generateUUIDv4();
        $_SESSION["token"] = $token;
        $sessionID = session_id();
        $agentHash = self::makeAgentHash();
        $lastRefresh = SystemDateTime::now();
        $validTo = $lastRefresh->adding(0, 0, 0, $properties["sessionLifetimeSeconds"]);

        DatabaseConnector::shared()->execute_query(
            "INSERT INTO session_tokens
            (token, session_id, user_id, agent_hash, session_id_refreshed_at, valid_to)
            VALUES (?, ?, ?, ?, ?, ?)",
            [
                $token,
                $sessionID,
                $userID,
                $agentHash,
                $lastRefresh->toDatabaseString(),
                $validTo->toDatabaseString()
            ]
        );

        Logger::log(LogLevel::info, "Generated token \"$token\" valid to {$validTo->toDatabaseString()} for session with ID \"$sessionID\" and agent hash \"$agentHash\" of user with ID \"$userID\".");
    }

    private static function startSessionWithOptions(): void {
        $properties = PropertiesReader::getProperties("authenticator");
        $options = [
            "name" => $properties["sessionName"],
            "sid_length" => $properties["sessionIDLength"],
            "gc_maxlifetime" => $properties["sessionLifetimeSeconds"],
            "cookie_lifetime" => $properties["sessionCookieLifetimeSeconds"],
            "cookie_domain" => $properties["sessionCookieDomain"],
            "cookie_path" => $properties["sessionCookiePath"],
            "cookie_secure" => $properties["sessionCookieHTTPSRequired"],
            "cookie_httponly" => self::SESSION_COOKIE_HTTPONLY,
            "cookie_samesite" => self::SESSION_COOKIE_SAMESITE
        ];

        session_start($options);
        Logger::log(LogLevel::info, "Session started.");
    }

    private static function extendSession(SystemDateTime $lastRefresh): void {
        $properties = PropertiesReader::getProperties("authenticator");
        $cookieOptions = [
            "expires" => time() + $properties["sessionCookieLifetimeSeconds"],
            "domain" => $properties["sessionCookieDomain"],
            "path" => $properties["sessionCookiePath"],
            "secure" => $properties["sessionCookieHTTPSRequired"],
            "httponly" => self::SESSION_COOKIE_HTTPONLY,
            "samesite" => self::SESSION_COOKIE_SAMESITE
        ];

        DatabaseConnector::shared()->execute_query(
            "UPDATE session_tokens
            SET session_id = ?, session_id_refreshed_at = ?, valid_to = ?
            WHERE token = ?",
            [
                session_id(),
                $lastRefresh->toDatabaseString(),
                SystemDateTime::now()
                    ->adding(0, 0, 0, $properties["sessionLifetimeSeconds"])
                    ->toDatabaseString(),
                $_SESSION["token"]
            ]
        );

        setcookie(session_name(), session_id(), $cookieOptions);
        Logger::log(LogLevel::info, "Session and its cookie validity have been extended.");
    }

    private static function endSession(): void {
        setcookie(session_name(), "", 1);
        session_unset();
        session_destroy();
        Logger::log(LogLevel::info, "Session ended and its cookie was deleted.");
    }

    private static function makeAgentHash(): string {
        $address = $_SERVER["REMOTE_ADDR"];
        $agent = $_SERVER["HTTP_USER_AGENT"];
        return md5("$address@$agent");
    }
}

?>