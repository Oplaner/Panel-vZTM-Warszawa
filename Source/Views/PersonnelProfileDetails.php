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
<?php

        $backAction = $profile->isActive() ? "/personnel" : "/personnel/all";

?>
        <h1><a href="<?php echo PathBuilder::action($backAction) ?>">&#8617;</a> <?php echo $profile->getOwner()->getFormattedLoginAndUsername() ?></h1>
        <div class="sectionContainer">
            <div class="section full">
                <h2><?php echo $profile->getDescription() ?></h2>
                <div class="flexLayout">
                    <div>
                        <p class="noBottomMargin"><b>Uprawnienia</b></p>
                        <ul>
<?php

                            foreach ($profile->getPrivileges() as $privilege):

?>
                            <li><?php echo $privilege->getDescription(Carrier::class) ?></li>
<?php

                            endforeach;

?>
                        </ul>
                    </div>
<?php

                    $statusClass = $profile->isActive() ? "active" : "inactive";
                    $statusText = $profile->isActive() ? "aktywny" : "nieaktywny";

?>
                    <div>
                        <p><span class="status <?php echo $statusClass ?>">profil&nbsp;<?php echo $statusText ?></span></p>
                    </div>
                </div>
<?php

                $activatedAt = $profile->getActivatedAt()->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithSeconds);
                $activatedBy = $profile->getActivatedBy()->getFormattedLoginAndUsername();

?>
                <div class="flexLayout">
                    <div>
                        <p><b>Data i godzina aktywacji</b><br><?php echo $activatedAt ?></p>
                        <p><b>Aktywowany przez</b><br><?php echo $activatedBy ?></p>
                    </div>    
<?php

                    if (!$profile->isActive()):
                    $deactivatedAt = $profile->getDeactivatedAt()->toLocalizedString(SystemDateTimeFormat::dateAndTimeWithSeconds);
                    $deactivatedBy = $profile->getDeactivatedBy()->getFormattedLoginAndUsername();

?>
                    <div>
                        <p><b>Data i godzina dezaktywacji</b><br><?php echo $deactivatedAt ?></p>
                        <p><b>Dezaktywowany przez</b><br><?php echo $deactivatedBy ?></p>
                    </div>
<?php

                    endif;

?>
                </div>
            </div>
        </div>
<?php

        if ($profile->isActive()):

?>
        <div class="toolbar bottom singleAction">
            <a href="<?php echo PathBuilder::action("/personnel/profile/{$profile->getID()}/deactivate") ?>" class="button destructive">Dezaktywuj profil</a>
        </div>
<?php

        endif;

?>
    </div>
</body>
</html>