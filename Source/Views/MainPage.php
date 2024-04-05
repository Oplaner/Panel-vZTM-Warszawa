<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Main</title>
</head>
<body>
<?php

    if (isset($_USER)):

?>
    <h1>Hello, <?php echo $_USER->getUsername() ?>!</h1>
    <a href="/Source/logout">Logout</a>
<?php

    else:

?>
    <h1>Hello!</h1>
<?php

        if (isset($showLogoutMessage) && $showLogoutMessage):

?>
    <p>Logged out successfully.</p>
<?php

        endif;

?>
    <form action="/Source/" method="POST">
        <input name="login" type="text"><br>
        <input name="password" type="password"><br>
        <input type="submit" value="Login">
    </form>
<?php

    endif;

?>
    <p><?php echo isset($authenticationResult) ? $authenticationResult->name : "" ?></p>
</body>
</html>