<?php

require_once __DIR__."/DatabaseConnector.php";
require_once __DIR__."/DatabaseEntity.php";
require_once __DIR__."/SystemDateTime.php";

final class User extends DatabaseEntity {
    private int $login;
    private string $username;
    private bool $shouldChangePassword;
    private ?array $profiles = null;
    private SystemDateTime $createdAt;

    private function __construct(?string $id, int $login, string $username, bool $shouldChangePassword, SystemDateTime $createdAt) {
        $this->setID($id);
        $this->login = $login;
        $this->username = $username;
        $this->shouldChangePassword = $shouldChangePassword;
        $this->createdAt = $createdAt;
    }

    public static function createNew(int $myBBUserID): ?User {
        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
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
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();

        return new User(null, $myBBUserID, $data["username"], true, SystemDateTime::now());
    }

    public static function withID(string $id): ?User {
        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT login, username, shouldChangePassword, createdAt
            FROM users
            WHERE id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            return null;
        }

        $data = $result->fetch_assoc();
        $result->free();

        return new User($id, $data["login"], $data["username"], $data["shouldChangePassword"], new SystemDateTime($data["createdAt"]));
    }

    public function getLogin(): int {
        return $this->login;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function updateUsername() {
        if ($this->isNew) {
            return;
        }

        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT m.username
            FROM mybb18_users AS m
            INNER JOIN users AS u
            ON m.uid = u.login
            WHERE u.id = ?",
            [
                $this->id
            ]
        );

        $data = $result->fetch_assoc();
        $result->free();

        if ($data["username"] != $this->username) {
            $this->username = $data["username"];
            $this->wasModified = true;
        }
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

    public function isActive(): bool {
        $activeProfiles = array_filter($this->getProfiles(), fn ($profile) => $profile->isActive());
        return count($activeProfiles) > 0;
    }

    public function getCreatedAt(): SystemDateTime {
        return $this->createdAt;
    }

    public function addProfile(Profile $profile): void {
        if (!in_array($profile, $this->getProfiles())) {
            $this->profiles[] = $profile;
            $this->wasModified = true;
        }
    }

    public function save(): bool {
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            return $db->execute_query(
                "INSERT INTO users
                (id, login, username, password, shouldChangePassword, createdAt)
                VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $this->id,
                    $this->login,
                    $this->username,
                    // TODO: Generate random temporary password. Consider its expiration date.
                    password_hash("password", PASSWORD_DEFAULT),
                    $this->shouldChangePassword,
                    $this->createdAt->toDatabaseString()
                ]
            );
        } elseif ($this->wasModified) {
            return $db->execute_query(
                "UPDATE users
                SET username = ?, shouldChangePassword = ?
                WHERE id = ?",
                [
                    $this->username,
                    $this->shouldChangePassword,
                    $this->id
                ]
            );
        }

        return true;
    }
}

?>