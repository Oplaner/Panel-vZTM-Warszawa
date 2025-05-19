<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo PathBuilder::stylesheet("style-light.css") ?>">
    <title>Panel vZTM Warszawa</title>
</head>
<body>
    <div id="loginPageContainer">
        <div id="loginForm">
            <a href="<?php echo PathBuilder::root() ?>">
                <img src="<?php echo PathBuilder::image("vztm-logo-full.svg") ?>" alt="Logo vZTM Warszawa">
            </a>
            <h1>Panel vZTM Warszawa</h1>
<?php

            if (isset($showLogoutMessage) && $showLogoutMessage):

?>
            <p class="successMessage">Pomyślnie wylogowano z systemu.</p>
<?php

            endif;

            if (isset($authenticationResult)):
                $authenticationError = "Dane logowania są nieprawidłowe.";

                if ($authenticationResult == AuthenticationResult::expiredPassword):
                    $authenticationError = "Twoje hasło wygasło. Użyj opcji resetu hasła.";
                endif;

?>
            <p class="errorMessage"><?php echo $authenticationError ?></p>
<?php

            endif;

?>
            <form action="<?php echo PathBuilder::action("/login") ?>" method="POST">
                <label for="login">Login:</label>
                <input type="text" id="login" name="login">
                <label for="password">Hasło:</label>
                <input type="password" id="password" name="password">
                <div class="buttonContainer">
                    <a href="<?php echo PathBuilder::action("/reset-password") ?>" class="button">Resetuj hasło</a>
                    <input type="submit" value="Zaloguj się">
                </div>
            </form>
        </div>
    </div>
</body>
</html>