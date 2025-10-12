<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [], null)

?>
<body>
    <div id="loginPageContainer">
        <div id="loginForm">
            <a href="<?php echo PathBuilder::root() ?>">
                <img src="<?php echo PathBuilder::image("vztm-logo-full.svg") ?>" alt="Logo vZTM Warszawa">
            </a>
            <h1>Panel vZTM Warszawa</h1>
            <form action="<?php echo PathBuilder::action("/login") ?>" method="POST">
<?php

                if (isset($showLogoutMessage) && $showLogoutMessage):

?>
                <p class="message success">Pomyślnie wylogowano z systemu.</p>
<?php

                endif;

                if (isset($authenticationResult)):
                $authenticationError = "Dane logowania są nieprawidłowe.";
                
                if ($authenticationResult == AuthenticationResult::accountInactive):
                $authenticationError = "Twoje konto jest nieaktywne.";
                elseif ($authenticationResult == AuthenticationResult::expiredPassword):
                $authenticationError = "Twoje hasło wygasło. Użyj opcji resetu hasła.";
                endif;

?>
                <p class="message error"><?php echo $authenticationError ?></p>
<?php

                endif;

?>
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