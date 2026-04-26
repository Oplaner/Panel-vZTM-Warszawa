<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::search], "Nowy dyrektor")

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1><a href="<?php echo PathBuilder::action("/personnel/directors") ?>">&#8617;</a> Nowy dyrektor</h1>
<?php

        if (isset($showMessage) && $showMessage):

?>
        <p class="message <?php echo $messageType ?>"><?php echo $message ?></p>
<?php

        endif;

?>
        <form action="<?php echo PathBuilder::action("/personnel/directors/new-profile") ?>" method="POST">
            <div class="sectionContainer column">
                <div class="section">
                    <h2>Użytkownik</h2>
<?php

                    ViewBuilder::buildSearchBox(
                        "/users/search/non-director",
                        1,
                        [],
                        true,
                        "directorLogin",
                        "",
                        "directorSearchBox",
                        "Wybierz użytkownika:",
                        "ID lub nazwa...",
                        5
                    );

?>
                    <p class="message info">Wyniki wyszukiwania obejmują wyłącznie użytkowników bez aktywnego profilu dyrektora.</p>
                </div>
                <div class="section">
                    <h2>Uprawnienia</h2>
                    <ul>
                        <li>Ma dostęp do wszystkich aspektów systemu</li>
                        <li>Może zostać zdezaktywowany przez innych dyrektorów</li>
                    </ul>
                </div>
            </div>
            <div class="toolbar bottom singleAction">
                <input type="submit" value="Utwórz">
            </div>
        </form>
    </div>
</body>
</html>