<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::search], "Nowy zakład")

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1><a href="<?php echo PathBuilder::action("/carriers") ?>">&#8617;</a> Nowy zakład</h1>
<?php

        if (isset($showMessage) && $showMessage):

?>
        <p class="message <?php echo $messageType ?>"><?php echo $message ?></p>
<?php

        endif;

?>
        <form action="<?php echo PathBuilder::action("/carriers/new") ?>" method="POST">
            <div class="sectionContainer">
                <div class="section wide">
                    <h2>Dane podstawowe</h2>
                    <label for="fullName" class="required">Nazwa pełna:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo $fullName ?>">
                    <label for="shortName" class="required">Nazwa skrócona:</label>
                    <input type="text" id="shortName" name="shortName" value="<?php echo $shortName ?>">
                    <p class="message info">Informacje dotyczące utworzenia zakładu (data i&nbsp;godzina, twórca) są zapisywane automatycznie.</p>
                </div>
                <div class="sectionContainer column narrow">
                    <div class="section">
                        <h2>Konfiguracja</h2>
                        <label for="numberOfTrialTasks" class="required">Liczba zadań do wykonania w trakcie okresu próbnego:</label>
                        <input type="text" id="numberOfTrialTasks" name="numberOfTrialTasks" value="<?php echo $numberOfTrialTasks ?>">
                        <label for="numberOfPenaltyTasks" class="required">Liczba zadań do wykonania w trakcie okresu karnego:</label>
                        <input type="text" id="numberOfPenaltyTasks" name="numberOfPenaltyTasks" value="<?php echo $numberOfPenaltyTasks ?>">
                    </div>
                    <div class="section">
                        <h2>Kierownicy</h2>
                        <div class="searchContainer" data-source="<?php echo PathBuilder::action("/users/search/all") ?>">
                            <div class="selectionContainer">
<?php

                                foreach ($supervisorSelections as $supervisorSelection):

?>
                                <div class="selection" data-key="<?php echo $supervisorSelection["key"] ?>">
                                    <span><?php echo $supervisorSelection["value"] ?></span>&nbsp;<a href="#">[&times;]</a>
                                </div>
<?php

                                endforeach;

?>
                            </div>
                            <input type="hidden" name="supervisorLoginsString" value="<?php echo $supervisorLoginsString ?>">
                            <label for="supervisorSearchBox">Dodaj kierownika:</label>
                            <div class="inputWithLoader">
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
            <div class="toolbar bottom singleAction">
                <input type="submit" value="Utwórz">
            </div>
        </form>
    </div>
</body>
</html>