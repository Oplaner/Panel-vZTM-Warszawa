<?php

enum AuthenticationResult {
    case accountInactive;
    case invalidCredentials;
    case expiredPassword;
    case success;
}

?>