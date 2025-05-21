    <div id="topBar">
        <div id="menuButton">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <img src="<?php echo PathBuilder::image("vztm-logo-short.svg") ?>" alt="Logo vZTM Warszawa">
        <div id="topBarUserInfo">
            #<?php echo $_USER->getLogin() ?> &bull; <?php echo $_USER->getUsername() ?><br>
            <a href="<?php echo PathBuilder::action("/logout") ?>">Wyloguj się</a>
        </div>
    </div>