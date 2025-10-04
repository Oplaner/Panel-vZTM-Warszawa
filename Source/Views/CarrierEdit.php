<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::search], $carrier->getFullName())

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1><a href="<?php echo PathBuilder::action("/carriers") ?>">&#8617;</a> <?php echo $carrier->getFullName() ?></h1>
        <form action="<?php echo PathBuilder::action("/carriers/{$carrier->getID()}/edit") ?>" method="POST">
            <div class="sectionContainer">
                <div class="section wide">
                    <h2>Dane podstawowe</h2>
                    <label for="fullName" class="required">Nazwa pełna:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo $carrier->getFullName() ?>">
                    <label for="shortName" class="required">Nazwa skrócona:</label>
                    <input type="text" id="shortName" name="shortName" value="<?php echo $carrier->getShortName() ?>">
<?php

                    $createdAt = $carrier->getCreatedAt()->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithoutSeconds);
                    $createdBy = $carrier->getCreatedBy()->getFormattedLoginAndUsername();

?>
                    <p><b>Data utworzenia</b><br><?php echo $createdAt ?></p>
                    <p><b>Utworzony przez</b><br><?php echo $createdBy ?></p>
                </div>
                <div class="sectionContainer column narrow">
                    <div class="section">
                        <h2>Konfiguracja</h2>
                        <label for="numberOfTrialTasks" class="required">Liczba zadań do wykonania w trakcie okresu próbnego:</label>
                        <input type="text" id="numberOfTrialTasks" name="numberOfTrialTasks" value="<?php echo $carrier->getNumberOfTrialTasks() ?>">
                        <label for="numberOfPenaltyTasks" class="required">Liczba zadań do wykonania w trakcie okresu karnego:</label>
                        <input type="text" id="numberOfPenaltyTasks" name="numberOfPenaltyTasks" value="<?php echo $carrier->getNumberOfPenaltyTasks() ?>">
                    </div>
                    <div class="section">
                        <h2>Kierownicy</h2>
<?php

                        $supervisors = $carrier->getSupervisors();
                        usort($supervisors, fn($a, $b) => $a->getLogin() <=> $b->getLogin());
                        $supervisorLoginsString = implode(
                            ";",
                            array_map(
                                fn($supervisor) => $supervisor->getLogin(),
                                $supervisors
                            )
                        );

?>
                        <div class="searchContainer" data-source="<?php echo PathBuilder::action("/users/search") ?>">
                            <div class="selectionContainer">
<?php

                                foreach ($supervisors as $supervisor):

?>
                                <div class="selection" data-key="<?php echo $supervisor->getLogin() ?>">
                                    <span><?php echo $supervisor->getFormattedLoginAndUsername() ?></span>&nbsp;<a href="#">[&times;]</a>
                                </div>
<?php

                                endforeach;

?>
                            </div>
                            <input type="hidden" name="supervisorLogins" value="<?php echo $supervisorLoginsString ?>">
                            <label for="supervisorSearchBox">Dodaj kierownika:</label>
                            <div class="inputWithLoader noBottomMargin">
                                <input type="text" id="supervisorSearchBox" placeholder="ID lub nazwa...">
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
                    </div>
                </div>
            </div>
            <div class="toolbar bottom">
                <a href="<?php echo PathBuilder::action("/carriers/{$carrier->getID()}") ?>" class="button">Anuluj</a>
                <input type="submit" value="Zapisz">
            </div>
        </form>
    </div>
</body>
</html>