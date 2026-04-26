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
<?php

                        ViewBuilder::buildSearchBox(
                            "/users/search/all",
                            null,
                            $supervisorSelections,
                            false,
                            "supervisorLoginsString",
                            $supervisorLoginsString,
                            "supervisorSearchBox",
                            "Dodaj kierownika:",
                            "ID lub nazwa...",
                            6
                        );

?>
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