<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu], $carrier->getFullName())

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
<?php

        $backAction = $carrier->isActive() ? "/carriers" : "/carriers/all";

?>
        <h1><a href="<?php echo PathBuilder::action($backAction) ?>">&#8617;</a> <?php echo $carrier->getFullName() ?></h1>
<?php

        if (isset($showMessage) && $showMessage):

?>
        <p class="message <?php echo $messageType ?>"><?php echo $message ?></p>
<?php

        endif;

?>
        <div class="sectionContainer">
            <div class="section wide">
                <h2>Dane podstawowe</h2>
                <div class="flexLayout">
                    <div>
                        <p><b>Nazwa pełna</b><br><?php echo $carrier->getFullName() ?></p>
                        <p><b>Nazwa skrócona</b><br><?php echo $carrier->getShortName() ?></p>
                    </div>
<?php

                    $statusClass = $carrier->isActive() ? "active" : "inactive";
                    $statusText = $carrier->isActive() ? "aktywny" : "zamknięty";

?>
                    <div>
                        <p><span class="status <?php echo $statusClass ?>">zakład&nbsp;<?php echo $statusText ?></span></p>
                    </div>
                </div>
<?php

                $createdAt = $carrier->getCreatedAt()->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithSeconds);
                $createdBy = $carrier->getCreatedBy()->getFormattedLoginAndUsername();

?>
                <div class="flexLayout">
                    <div>
                        <p><b>Data i godzina utworzenia</b><br><?php echo $createdAt ?></p>
                        <p><b>Utworzony przez</b><br><?php echo $createdBy ?></p>
                    </div>    
<?php

                    if (!$carrier->isActive()):
                    $closedAt = $carrier->getClosedAt()->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithSeconds);
                    $closedBy = $carrier->getClosedBy()->getFormattedLoginAndUsername();

?>
                    <div>
                        <p><b>Data i godzina zamknięcia</b><br><?php echo $closedAt ?></p>
                        <p><b>Zamknięty przez</b><br><?php echo $closedBy ?></p>
                    </div>
<?php

                    endif;

?>
                </div>
            </div>
            <div class="sectionContainer column narrow">
                <div class="section">
                    <h2>Konfiguracja</h2>
                    <p><b>Liczba zadań do wykonania w trakcie okresu próbnego</b><br><?php echo $carrier->getNumberOfTrialTasks() ?></p>
                    <p><b>Liczba zadań do wykonania w trakcie okresu karnego</b><br><?php echo $carrier->getNumberOfPenaltyTasks() ?></p>
                </div>
                <div class="section">
                    <h2>Kierownicy</h2>
<?php

                    $supervisors = $carrier->getSupervisors();

                    if (empty($supervisors)):

?>
                    <p>Zakład nie ma przypisanego kierownika.</p>
<?php

                    else:
?>
                    <ul>
<?php

                        foreach ($supervisors as $supervisor):

?>
                        <li><?php echo $supervisor->getUsername() ?></li>
<?php

                        endforeach;

?>
                    </ul>
<?php

                    endif;

?>
                </div>
            </div>
        </div>
<?php

        if ($carrier->isActive()):

?>
        <div class="toolbar bottom">
            <a href="<?php echo PathBuilder::action("/carriers/{$carrier->getID()}/edit") ?>" class="button">Edytuj</a>
            <a href="<?php echo PathBuilder::action("/carriers/{$carrier->getID()}/close") ?>" class="button destructive">Zamknij zakład</a>
        </div>
<?php

        endif;

?>
    </div>
</body>
</html>