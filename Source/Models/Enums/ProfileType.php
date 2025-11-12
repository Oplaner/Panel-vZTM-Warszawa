<?php

enum ProfileType: string {
    case director = "DIRECTOR";
    case driver = "DRIVER";
    case personnel = "PERSONNEL";

    public function getClass(): string {
        return match ($this) {
            self::director => DirectorProfile::class,
            self::driver => DriverProfile::class,
            self::personnel => PersonnelProfile::class
        };
    }
}

?>