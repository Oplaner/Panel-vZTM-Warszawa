<?php

final class Authenticator {
    private const SESSION_COOKIE_HTTPONLY = true;
    private const SESSION_COOKIE_SAMESITE = "Lax";
    private const TEMPORARY_PASSWORD_CHARACTER_GROUPS = [
        "uppercaseLetters" => "ABCDEFGHJKLMNPQRTUVWXY",
        "lowercaseLetters" => "abcdefghjkmnpqrstuvwxyz",
        "digits" => "346789",
        "specialCharacters" => "!@#$%&?"
    ];

    public function __construct() {}

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

                $result = DatabaseConnector::shared()->execute_query(
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
                    self::endSession();
                } else {
                    $data = $result->fetch_assoc();
                    $result->free();
                    $userID = $data["user_id"];
                    $lastRefresh = new SystemDateTime($data["session_id_refreshed_at"]);
                    $shouldRefresh = $lastRefresh
                        ->adding(0, 0, 0, $properties["sessionIDRefreshIntervalSeconds"])
                        ->isBefore(SystemDateTime::now());

                    if ($shouldRefresh) {
                        session_regenerate_id(true);
                        $lastRefresh = SystemDateTime::now();
                    }

                    self::extendSession($lastRefresh);
                    $_USER = User::withID($userID);
                }
            } else {
                self::endSession();
            }
        }

        if (is_a($_USER, User::class)) {
            Logger::log(LogLevel::info, "Found session data for user with ID \"{$_USER->getID()}\".");
        } else {
            Logger::log(LogLevel::info, "Session data were not found. Continuing without a user.");
        }

        return $_USER;
    }

    public static function authenticateUser(string $login, string $password): AuthenticationResult {
        global $_USER;

        $result = DatabaseConnector::shared()->execute_query(
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
                    self::startUserSession($userID);
                    $_USER = User::withID($userID);
                    Logger::log(LogLevel::info, "Successfully authenticated user: $_USER.");
                    return AuthenticationResult::success;
                } else {
                    Logger::log(LogLevel::info, "Failed to authenticate user with login \"$login\".");
                    return AuthenticationResult::expiredPassword;
                }
            }
        } else {
            $result->free();
        }

        Logger::log(LogLevel::info, "Failed to authenticate user with login \"$login\".");
        return AuthenticationResult::invalidCredentials;
    }

    public static function endUserSession(): void {
        global $_USER;
        $token = $_SESSION["token"];

        DatabaseConnector::shared()->execute_query(
            "DELETE FROM session_tokens
            WHERE token = ?",
            [
                $token
            ]
        );

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
        DatabaseConnector::shared()->execute_query(
            "DELETE FROM session_tokens
            WHERE valid_to <= ?",
            [
                SystemDateTime::now()->toDatabaseString()
            ]
        );
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
    }

    private static function endSession(): void {
        setcookie(session_name(), "", 1);
        session_unset();
        session_destroy();
    }

    private static function makeAgentHash(): string {
        $address = $_SERVER["REMOTE_ADDR"];
        $agent = $_SERVER["HTTP_USER_AGENT"];
        return md5("$address@$agent");
    }
}

?>