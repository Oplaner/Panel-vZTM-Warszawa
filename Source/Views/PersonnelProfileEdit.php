<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu], $profile->getOwner()->getFormattedLoginAndUsername())

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1><a href="<?php echo PathBuilder::action("/personnel") ?>">&#8617;</a> <?php echo $profile->getOwner()->getFormattedLoginAndUsername() ?></h1>
<?php

        if (isset($showMessage) && $showMessage):

?>
        <p class="message error"><?php echo $message ?></p>
<?php

        endif;

?>
        <form action="<?php echo PathBuilder::action("/personnel/profile/{$profile->getID()}/edit") ?>" method="POST">
            <div class="sectionContainer column">
                <div class="section">
                    <h2>Pracownik</h2>
                    <label for="description" class="required">Opis funkcji:</label>
                    <input type="text" id="description" name="description" value="<?php echo $description ?>">
                    <p class="message info">Zmiana danych spowoduje dezaktywację obecnego i&nbsp;utworzenie nowego profilu personelu dla tego pracownika.</p>
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
            <div class="toolbar bottom">
                <a href="<?php echo PathBuilder::action("/personnel/profile/{$profile->getID()}") ?>" class="button">Anuluj</a>
                <input type="submit" value="Zapisz">
            </div>
        </form>
    </div>
</body>
</html>