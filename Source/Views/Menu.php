    <div id="menu">
        <div class="menuSection">
            <h2>Kierownik R-1</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-2</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-3</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-4</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-5</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-6</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
<?php

        if ($_USER->hasActiveProfileOfType(DirectorProfile::class)):

?>
        <div class="menuSection">
            <h2>Dyrektor</h2>
            <ul>
                <li><a href="#">Personel</a></li>
                <li><a href="<?php echo PathBuilder::action("/carriers") ?>">Przewoźnicy</a></li>
                <li><a href="#">Ustawienia</a></li>
            </ul>
        </div>
<?php

        endif;

?>
    </div>