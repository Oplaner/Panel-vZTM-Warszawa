<?php

enum AuthenticationResult {
    case invalidCredentials;
    case expiredPassword;
    case success;
}

?>