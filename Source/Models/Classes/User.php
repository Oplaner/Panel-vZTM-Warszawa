<?php

final class User extends DatabaseEntity {
    private int $login;
    private string $username;
    private ?string $temporaryPassword = null;
    private ?SystemDateTime $temporaryPasswordValidTo = null;
    private bool $shouldChangePassword;
    private SystemDateTime $createdAt;

    private function __construct(?string $id, int $login, string $username, bool $shouldChangePassword, SystemDateTime $createdAt) {
        parent::__construct($id);
        $this->login = $login;
        $this->username = $username;
        $this->shouldChangePassword = $shouldChangePassword;
        $this->createdAt = $createdAt;
        $this->save();
    }

    public static function createNew(int $myBBUserID): ?User {
        Logger::log(LogLevel::info, "Creating new user with myBB ID $myBBUserID.");
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT m.username
            FROM mybb18_users AS m
            LEFT JOIN users AS u
            ON m.uid = u.login
            WHERE m.uid = ? AND u.login IS NULL",
            [
                $myBBUserID
            ]
        );
        
        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find myBB user with ID $myBBUserID.");
            $result->free();
            return null;
        }

        $user = new User(null, $myBBUserID, $result->fetch_column(), true, SystemDateTime::now());
        $result->free();
        Logger::log(LogLevel::info, "Created new user from myBB: $user.");
        return $user;
    }

    public static function withID(string $id): ?User {
        $cachedObject = self::findCached($id);

        if (is_a($cachedObject, User::class)) {
            return $cachedObject;
        }

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT login, username, should_change_password, created_at
            FROM users
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::warn, "Could not find user with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        return new User($id, $data["login"], $data["username"], $data["should_change_password"], new SystemDateTime($data["created_at"]));
    }

    public function getLogin(): int {
        return $this->login;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function updateUsername(): void {
        if ($this->isNew) {
            return;
        }

        Logger::log(LogLevel::info, "Updating username of user with ID \"{$this->id}\".");

        $result = DatabaseConnector::shared()->execute_query(
            "SELECT m.username
            FROM mybb18_users AS m
            INNER JOIN users AS u
            ON m.uid = u.login
            WHERE u.id = ?",
            [
                $this->id
            ]
        );

        $username = $result->fetch_column();
        $result->free();

        if ($username != $this->username) {
            $this->username = $username;
            $this->wasModified = true;
            $this->save();
        }
    }

    public function getTemporaryPassword(): ?string {
        return $this->temporaryPassword;
    }

    public function getTemporaryPasswordValidTo(): ?SystemDateTime {
        return $this->temporaryPasswordValidTo;
    }

    public function shouldChangePassword(): bool {
        return $this->shouldChangePassword;
    }

    public function getAllContracts(): array {
        return Contract::getAllByDriver($this);
    }

    public function getActiveContracts(): array {
        return Contract::getActiveByDriver($this);
    }

    public function getAllProfiles(): array {
        return Profile::getAllByUser($this);
    }

    public function getActiveProfiles(): array {
        return Profile::getActiveByUser($this);
    }

    public function hasActiveProfileOfType(string $profileClass): bool {
        return array_any(
            $this->getActiveProfiles(),
            fn($profile) => is_a($profile, $profileClass)
        );
    }

    public function getCreatedAt(): SystemDateTime {
        return $this->createdAt;
    }

    public function isActive(): bool {
        return count($this->getActiveProfiles()) > 0;
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", login: %d, username: \"%s\", shouldChangePassword: %s, createdAt: %s)",
            $this->id,
            $this->login,
            $this->username,
            $this->shouldChangePassword ? "true" : "false",
            $this->createdAt->toDatabaseString()
        );
    }

    protected function save(): void {
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            $properties = PropertiesReader::getProperties("authenticator");
            $this->temporaryPassword = Authenticator::generateTemporaryPassword();
            $this->temporaryPasswordValidTo = SystemDateTime::now()->adding(
                $properties["temporaryPasswordValidityDays"],
                $properties["temporaryPasswordValidityHours"],
                $properties["temporaryPasswordValidityMinutes"]
            );

            $db->execute_query(
                "INSERT INTO users
                (id, login, username, password, password_valid_to, should_change_password, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->login,
                    $this->username,
                    password_hash($this->temporaryPassword, PASSWORD_DEFAULT),
                    $this->temporaryPasswordValidTo->toDatabaseString(),
                    $this->shouldChangePassword,
                    $this->createdAt->toDatabaseString()
                ]
            );
            $this->isNew = false;
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE users
                SET username = ?, should_change_password = ?
                WHERE id = ?",
                [
                    $this->username,
                    $this->shouldChangePassword,
                    $this->id
                ]
            );
            $this->wasModified = false;
        }
    }
}

?>