<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu], "Personel")

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1>Personel</h1>
        <div class="toolbar">
            <div>
                <div class="inputContainer">
                    <input type="radio" id="showUsersWithPersonnelProfile" name="personnelType">
                    <label for="showUsersWithPersonnelProfile">Pracownicy funkcyjni</label>
                </div>
                <div class="inputContainer">
                    <input type="radio" id="showUsersWithDirectorProfile" name="personnelType">
                    <label for="showUsersWithDirectorProfile">Dyrektorzy</label>
                </div>
            </div>
            <a href="#" class="button">TODO</a>
        </div>
    </div>
</body>
</html>