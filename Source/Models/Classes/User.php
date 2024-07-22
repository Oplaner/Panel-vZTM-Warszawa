<?php

final class User extends DatabaseEntity {
    private int $login;
    private string $username;
    private ?string $temporaryPassword = null;
    private ?SystemDateTime $temporaryPasswordValidTo = null;
    private bool $shouldChangePassword;
    private ?array $profiles = null;
    private SystemDateTime $createdAt;

    private function __construct(?string $id, int $login, string $username, bool $shouldChangePassword, SystemDateTime $createdAt) {
        parent::__construct($id);
        $this->login = $login;
        $this->username = $username;
        $this->shouldChangePassword = $shouldChangePassword;
        $this->createdAt = $createdAt;
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
            Logger::log(LogLevel::info, "Could not find new myBB user with ID $myBBUserID.");
            $result->free();
            return null;
        }

        $user = new User(null, $myBBUserID, $result->fetch_column(), true, SystemDateTime::now());
        $result->free();
        Logger::log(LogLevel::info, "Created new user from myBB: $user.");
        return $user;
    }

    public static function withID(string $id): ?User {
        Logger::log(LogLevel::info, "Fetching existing user with ID \"$id\".");
        $result = DatabaseConnector::shared()->execute_query(
            "SELECT login, username, should_change_password, created_at
            FROM users
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find existing user with ID \"$id\".");
            $result->free();
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();
        $user = new User($id, $data["login"], $data["username"], $data["should_change_password"], new SystemDateTime($data["created_at"]));
        Logger::log(LogLevel::info, "Fetched existing user: $user.");
        return $user;
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

    public function getProfiles(): array {
        if (is_null($this->profiles)) {
            // TODO: Download profiles.
            $this->profiles = [];
        }

        return $this->profiles;
    }

    public function addProfile(Profile $profile): void {
        if (!in_array($profile, $this->getProfiles())) {
            $this->profiles[] = $profile;
            $this->wasModified = true;
        }
    }

    public function getCreatedAt(): SystemDateTime {
        return $this->createdAt;
    }

    public function isActive(): bool {
        $activeProfiles = array_filter($this->getProfiles(), fn ($profile) => $profile->isActive());
        return count($activeProfiles) > 0;
    }

    public function save(): void {
        Logger::log(LogLevel::info, "Saving ".($this->isNew ? "new" : "existing")." user: $this.");
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
        }
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", login: %d, temporaryPassword: %s, temporaryPasswordValidTo: %s, shouldChangePassword: %s, profiles: (%d), createdAt: %s, isNew: %s, wasModified: %s)",
            $this->id,
            $this->login,
            is_null($this->temporaryPassword) ? "null" : "\"".$this->temporaryPassword."\"",
            is_null($this->temporaryPasswordValidTo) ? "null" : $this->getTemporaryPasswordValidTo()->toDatabaseString(),
            $this->shouldChangePassword() ? "true" : "false",
            is_null($this->profiles) ? 0 : count($this->profiles),
            $this->createdAt->toDatabaseString(),
            $this->isNew ? "true" : "false",
            $this->wasModified ? "true" : "false"
        );
    }
}

?>