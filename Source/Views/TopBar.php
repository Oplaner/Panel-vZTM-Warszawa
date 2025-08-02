    <div id="topBar">
        <div id="menuButton">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <div id="topBarLogo">
            <a href="<?php echo PathBuilder::root() ?>">
                <img src="<?php echo PathBuilder::image("vztm-logo-short.svg") ?>" alt="Logo vZTM Warszawa">
            </a>
        </div>
        <div id="topBarUserInfo">
            <?php echo $_USER->getFormattedLoginAndUsername() ?><br>
            <a href="<?php echo PathBuilder::action("/logout") ?>">Wyloguj się</a>
        </div>
    </div>