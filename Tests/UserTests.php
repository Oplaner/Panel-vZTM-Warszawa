<?php

require("../Source/Models/Classes/User.php");

final class UserTests {
    public static function createNewUser(): bool|string {
        $user = User::createNew();

        if (!is_a($user, User::class)) {
            return "Expected a ".User::class." object. Found: ".gettype($user).".";
        } else {
            return true;
        }
    }

    public static function checkNewUserIDPattern(): bool|string {
        $user = User::createNew();
        $id = $user->getID();
        $pattern = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/";

        if (!preg_match($pattern, $id)) {
            return "New user's ID \"$id\" does not match correct pattern: $pattern.";
        } else {
            return true;
        }
    }
}

?>