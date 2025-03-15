<?php

#[Attribute(Attribute::TARGET_METHOD)]
final class Access {
    public readonly AccessGroup $group;
    public readonly ?array $profiles;
    public readonly ?array $allowedPersonnelPrivileges;
    public readonly ?string $carrierKey;

    public function __construct(AccessGroup $group, ?array $profiles = null, ?array $allowedPersonnelPrivileges = null, ?string $carrierKey = null) {
        $this->group = $group;
        $this->profiles = $profiles;
        $this->allowedPersonnelPrivileges = $allowedPersonnelPrivileges;
        $this->carrierKey = $carrierKey;
    }
}