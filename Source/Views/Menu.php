    <div id="menu">
<?php

        if ($_USER->hasActiveProfileOfType(PersonnelProfile::class)):
        $supervisedCarriers = [];

        foreach (Carrier::getAllActive() as $carrier):
        foreach ($carrier->getSupervisors() as $supervisor):
        if ($supervisor->getID() == $_USER->getID()):
        $supervisedCarriers[] = $carrier;
        endif;
        endforeach;
        endforeach;

        foreach ($supervisedCarriers as $supervisedCarrier):
?>
        <div>
            <h2>Kierownik <?php echo $supervisedCarrier->getShortName() ?></h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
<?php

        endforeach;
        endif;

        if ($_USER->hasActiveProfileOfType(DirectorProfile::class)):

?>
        <div>
            <h2>Dyrektor</h2>
            <ul>
                <li><a href="#">Personel</a></li>
                <li><a href="<?php echo PathBuilder::action("/carriers") ?>">Zakłady</a></li>
                <li><a href="#">Ustawienia</a></li>
            </ul>
        </div>
<?php

        endif;

?>
    </div>