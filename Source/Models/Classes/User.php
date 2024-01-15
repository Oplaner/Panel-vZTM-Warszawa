<?php

require_once __DIR__."/DatabaseEntity.php";
require_once __DIR__."/SystemDateTime.php";

final class User extends DatabaseEntity {
    private int $login;
    private string $username;
    private bool $shouldChangePassword;
    private ?array $profiles = null;
    private SystemDateTime $createdAt;

    private function __construct(?string $id, int $login, string $username, bool $shouldChangePassword = true) {
        $this->setID($id);
        $this->login = $login;
        $this->username = $username;
        $this->shouldChangePassword = $this->isNew || $shouldChangePassword;
        $this->createdAt = SystemDateTime::now();
    }

    public static function createNew(int $myBBUserID): ?User {
        // TODO: Check if there is myBB user with given ID. If so, get their username and create User.
        return new User(null, $myBBUserID, "user-$myBBUserID");
    }

    public static function withID(string $id): ?User {
        // TODO: Get user from database. If not found, return null.
        $user = new User($id, 1387, "Oplaner", false);
        // TODO: Configure with obtained data.
        return $user;
    }

    public function getLogin(): int {
        return $this->login;
    }

    public function getUsername(): string {
        return $this->username;
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
        // Try to save this entity to database.
        return true;
    }
}

?>