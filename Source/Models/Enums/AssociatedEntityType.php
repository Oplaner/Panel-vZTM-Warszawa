<?php

enum AssociatedEntityType: string {
    case carrier = "CARRIER";

    public function getClass(): string {
        return match ($this) {
            self::carrier => Carrier::class
        };
    }
}

?>