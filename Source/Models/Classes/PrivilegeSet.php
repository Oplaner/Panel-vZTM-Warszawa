<?php

require_once __DIR__."/DatabaseConnector.php";
require_once __DIR__."/DatabaseEntity.php";
require_once __DIR__."/Logger.php";
require_once __DIR__."/Privilege.php";
require_once __DIR__."/SystemDateTime.php";
require_once __DIR__."/../Enums/LogLevel.php";

final class PrivilegeSet extends DatabaseEntity {
    private string $profileID;
    private array $privileges;
    private SystemDateTime $validFrom;
    private ?SystemDateTime $validTo;

    private function __construct(?string $id, string $profileID, array $privileges, ?SystemDateTime $validFrom = null, ?SystemDateTime $validTo = null) {
        $this->setID($id);
        $this->profileID = $profileID;
        $this->privileges = $privileges;
        $this->validFrom = is_null($validFrom) ? SystemDateTime::now() : $validFrom;
        $this->validTo = $validTo;
    }

    public static function createNew(string $profileID, array $privileges): PrivilegeSet {
        if (count($privileges) == 0) {
            throw new Exception("Creating a privilege set with 0 privileges is not allowed.");
        }

        return new PrivilegeSet(null, $profileID, $privileges);
    }

    public static function withID(string $id): ?PrivilegeSet {
        Logger::log(LogLevel::info, "Fetching privilege set with ID \"$id\".");
        $db = DatabaseConnector::shared();
        $result = $db->execute_query(
            "SELECT ps.profile_id, ps.valid_from, ps.valid_to, psp.privilege_id
            FROM privilege_sets AS ps
            INNER JOIN privilege_set_privileges AS psp
            ON ps.id = psp.privilege_set_id
            WHERE ps.id = ?",
            [
                $id
            ]
        );

        if ($result->num_rows == 0) {
            Logger::log(LogLevel::info, "Could not find privilege set with ID \"$id\".");
            $result->free();
            return null;
        }

        $profileID = null;
        $privileges = [];
        $validFrom = null;
        $validTo = null;

        while ($data = $result->fetch_assoc()) {
            if (is_null($profileID)) {
                $profileID = $data["profile_id"];
                $validFrom = new SystemDateTime($data["valid_from"]);

                if (!is_null($data["valid_to"])) {
                    $validTo = new SystemDateTime($data["valid_to"]);
                }
            }

            $privilege = Privilege::withID($data["privilege_id"]);

            if (!is_null($privilege)) {
                $privileges[] = $privilege;
            }
        }
        
        $result->free();
        $privilegeSet = new PrivilegeSet($id, $profileID, $privileges, $validFrom, $validTo);
        Logger::log(LogLevel::info, "Fetched privilege set: $privilegeSet.");
        return $privilegeSet;
    }

    public function getProfileID(): string {
        return $this->profileID;
    }

    public function getPrivileges(): array {
        return $this->privileges;
    }

    public function getValidFrom(): SystemDateTime {
        return $this->validFrom;
    }

    public function getValidTo(): ?SystemDateTime {
        return $this->validTo;
    }

    public function setValidTo(SystemDateTime $validTo): void {
        $this->validTo = $validTo;
        $this->wasModified = true;
    }

    public function save(): void {
        Logger::log(LogLevel::info, "Saving ".($this->isNew ? "new" : "existing")." privilege set: $this.");
        $db = DatabaseConnector::shared();

        if ($this->isNew) {
            foreach ($this->privileges as $privilege) {
                /*
                    Privileges themselves are expected to have been created before they are being used
                    in this context, so for this reason there is no additional save() call on each.
                */
                $db->execute_query(
                    "INSERT INTO privilege_set_privileges
                    (privilege_set_id, privilege_id)
                    VALUES (?, ?)",
                    [
                        $this->id,
                        $privilege->getID()
                    ]
                );
            }

            $db->execute_query(
                "INSERT INTO privilege_sets
                (id, profile_id, valid_from, valid_to)
                VALUES (?, ?, ?, ?)",
                [
                    $this->id,
                    $this->profileID,
                    $this->validFrom->toDatabaseString(),
                    is_null($this->validTo) ? null : $this->validTo->toDatabaseString()
                ]
            );
        } elseif ($this->wasModified) {
            $db->execute_query(
                "UPDATE privilege_sets
                SET valid_to = ?
                WHERE id = ?",
                [
                    $this->validTo->toDatabaseString(),
                    $this->id
                ]
            );
        }
    }

    public function __toString() {
        return sprintf(
            __CLASS__."(id: \"%s\", profileID: \"%s\", privileges: (%d), validFrom: %s, validTo: %s)",
            $this->id,
            $this->profileID,
            count($this->privileges),
            $this->validFrom->toDatabaseString(),
            is_null($this->validTo) ? "null" : $this->validTo->toDatabaseString()
        );
    }
}

?>