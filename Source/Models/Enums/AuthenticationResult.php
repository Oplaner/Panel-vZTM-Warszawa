<?php

enum AuthenticationResult {
    case accountInactive;
    case expiredPassword;
    case invalidCredentials;
    case success;
}

?>