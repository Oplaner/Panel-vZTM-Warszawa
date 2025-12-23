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
                    <h2>Pracownik</h2>
                    <div class="searchContainer" data-source="<?php echo PathBuilder::action("/users/search/non-personnel") ?>" data-selection-limit="1">
                        <div class="selectionContainer">
<?php

                            if (!is_null($personnelSelection)):

?>
                            <div class="selection" data-key="<?php echo $personnelSelection["key"] ?>">
                                <span><?php echo $personnelSelection["value"] ?></span>&nbsp;<a href="#">[&times;]</a>
                            </div>
<?php

                            endif;

?>
                        </div>
                        <input type="hidden" name="personnelLogin" value="<?php echo $personnelLogin ?>">
                        <label for="personnelSearchBox" class="required">Wybierz pracownika:</label>
                        <div class="inputWithLoader">
<?php

                            $disabled = !is_null($personnelSelection) ? " disabled" : "";

?>
                            <input type="text" id="personnelSearchBox" placeholder="ID lub nazwa..." <?php echo $disabled ?>>
                            <div class="loaderContainer">
                                <div class="loader"></div>
                            </div>
                        </div>
                        <div class="searchMatchesContainer">
                            <div class="searchMatchesScrollContainer">
                                <div class="searchMatches"></div>
                            </div>
                        </div>
                    </div>
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