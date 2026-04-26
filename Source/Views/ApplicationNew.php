<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [], null)

?>
<body>
    <div id="content" class="noTopBar">
        <div id="centeredLogo">
            <a href="<?php echo PathBuilder::root() ?>">
                <img src="<?php echo PathBuilder::image("vztm-logo-full.svg") ?>" alt="Logo vZTM Warszawa">
            </a>
        </div>
        <h1><a href="<?php echo PathBuilder::root() ?>">&#8617;</a> Zostań kierowcą</h1>
<?php

        if (isset($showMessage) && $showMessage):

?>
        <p class="message <?php echo $messageType ?>"><?php echo $message ?></p>
<?php

        endif;

?>
        <form action="<?php echo PathBuilder::action("/applications/new") ?>" method="POST">
            <div class="sectionContainer">
                <div class="section narrow">
<?php

                    ViewBuilder::buildSearchBox(
                        "/users/search/driver-candidate",
                        1,
                        is_null($candidateSelection) ? [] : [$candidateSelection],
                        true,
                        "candidateLogin",
                        $candidateLogin,
                        "candidateSearchBox",
                        "Wybierz użytkownika:",
                        "ID lub nazwa...",
                        5
                    );

?>
                    <p class="message info">Wyniki wyszukiwania obejmują wyłącznie tych użytkowników, którzy nie mają aktywnej lub oczekującej na rozpatrzenie aplikacji.</p>
                    <label for="day" class="required">Data urodzenia:</label>
<?php

                    $properties = PropertiesReader::getProperties("application");
                    $maxYear = (int) SystemDateTime::now()->toLocalizedString(SystemDateTimeFormat::year);
                    $minYear = $maxYear - $properties["dateOfBirthMinYearOffset"];
                    ViewBuilder::buildDatePicker($day, $month, $year, $maxYear, $minYear, 5);

?>
                </div>
                <div class="section wide">
                    <label for="passedExamProofURL" class="required">Link do wyniku egzaminu WORD:</label>
                    <input type="text" id="passedExamProofURL" name="passedExamProofURL" value="<?php echo $passedExamProofURL ?>">
                    <label for="motivation" class="required">Motywacja &mdash; dlaczego chcesz zostać kierowcą:</label>
                    <textarea id="motivation" name="motivation"><?php echo $motivation ?></textarea>
                    <p class="message info">Twój wniosek zostanie rozpatrzony przez Dyrektora vZTM Warszawa.</p>
                </div>
            </div>
            <div class="toolbar bottom singleAction">
                <input type="submit" value="Wyślij aplikację">
            </div>
        </form>
    </div>
</body>
</html>