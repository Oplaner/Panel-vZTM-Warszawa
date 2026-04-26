<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::search], "Nowy pracownik funkcyjny")

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1><a href="<?php echo PathBuilder::action("/personnel") ?>">&#8617;</a> Nowy pracownik funkcyjny</h1>
<?php

        if (isset($showMessage) && $showMessage):

?>
        <p class="message <?php echo $messageType ?>"><?php echo $message ?></p>
<?php

        endif;

?>
        <form action="<?php echo PathBuilder::action("/personnel/new-profile") ?>" method="POST">
            <div class="sectionContainer column">
                <div class="section">
                    <h2>Użytkownik</h2>
<?php

                    ViewBuilder::buildSearchBox(
                        "/users/search/non-personnel",
                        1,
                        is_null($personnelSelection) ? [] : [$personnelSelection],
                        true,
                        "personnelLogin",
                        $personnelLogin,
                        "personnelSearchBox",
                        "Wybierz użytkownika:",
                        "ID lub nazwa...",
                        5
                    );

?>
                    <p class="message info">Wyniki wyszukiwania obejmują wyłącznie użytkowników bez aktywnego profilu personelu.</p>
                    <label for="description" class="required">Opis funkcji:</label>
                    <input type="text" id="description" name="description" value="<?php echo $description ?>">
                </div>
                <div class="section">
                    <h2>Uprawnienia</h2>
<?php

                    for ($i = 0; $i < count($privilegeGroups); $i++):
                    foreach ($privilegeGroups[$i] as $privilege):
                    $privilegeID = $privilege->getID();
                    $privilegeDescription = $privilege->getDescription();
                    $checked = in_array($privilege, $privileges) ? " checked" : "";

?>
                    <div class="inputContainer">
                        <input type="checkbox" id="<?php echo $privilegeID ?>" name="privileges[<?php echo $privilegeID ?>]"<?php echo $checked ?>>
                        <label for="<?php echo $privilegeID ?>"><?php echo $privilegeDescription ?></label>
                    </div>
<?php

                    endforeach;
                    if ($i < count($privilegeGroups) - 1):

?>
                    <div class="inputSeparator"></div>
<?php

                    endif;
                    endfor;

?>
                </div>
            </div>
            <div class="toolbar bottom singleAction">
                <input type="submit" value="Utwórz">
            </div>
        </form>
    </div>
</body>
</html>