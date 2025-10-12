<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu], $pageSubtitle)

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1><a href="<?php echo PathBuilder::action($backAction) ?>">&#8617;</a> <?php echo $title ?></h1>
        <form action="<?php echo PathBuilder::action($formAction) ?>" method="POST">
            <input type="hidden" name="confirmed" value="true">
            <div class="sectionContainer">
                <div class="section full">
                    <h2>Potwierdzenie</h2>
                    <p><?php echo $confirmationMessage ?></p>
<?php
                    if (isset($infoMessage)):
?>
                    <p class="message info"><?php echo $infoMessage ?></p>
<?php

                    endif;

?>
                </div>
            </div>
            <div class="toolbar bottom">
                <a href="<?php echo PathBuilder::action($cancelAction) ?>" class="button">Anuluj</a>
                <input type="submit" value="<?php echo $submitButton ?>">
            </div>
        </form>
    </div>
</body>
</html>