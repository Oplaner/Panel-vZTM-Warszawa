<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::redirect], "Dyrektorzy")

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
                    <input type="radio" id="showPersonnelProfiles" name="personnelType" data-redirect="<?php echo PathBuilder::action("/personnel") ?>">
                    <label for="showPersonnelProfiles">Pracownicy funkcyjni</label>
                </div>
                <div class="inputContainer">
                    <input type="radio" id="showDirectorProfiles" name="personnelType" checked>
                    <label for="showDirectorProfiles">Dyrektorzy</label>
                </div>
                <div class="inputSeparator"></div>
                <div class="inputContainer">
<?php

                $checked = $showingActiveOnly ? " checked" : "";
                $redirectAction = $showingActiveOnly ? "/personnel/directors/all" : "/personnel/directors";

?>
                <input type="checkbox" id="showActiveProfilesOnly" data-redirect="<?php echo PathBuilder::action($redirectAction) ?>"<?php echo $checked ?>>
                <label for="showActiveProfilesOnly">Pokaż tylko aktywne profile</label>
            </div>
            </div>
            <a href="#" class="button">Nadaj uprawnienia</a>
        </div>
        <table>
            <tr>
                <th>Status</th>
                <th>Dyrektor</th>
                <th>Rodzaj</th>
                <th class="optional">Ważny od</th>
<?php

                if (!$showingActiveOnly):

?>
                <th class="optional">Ważny do</th>
<?php

                endif;

?>
                <th>&nbsp;</th>
                <th class="summary">&nbsp;</th>
            </tr>
<?php

            if (count($profiles) == 0):
            $colspan = $showingActiveOnly ? 6 : 7;

?>
            <tr>
                <td colspan="<?php echo $colspan ?>">Brak danych do wyświetlenia.</td>
            </tr>
<?php

            else:
            foreach ($profiles as $profile):
            $statusClass = $profile->isActive() ? "active" : "inactive";
            $statusText = $profile->isActive() ? "aktywny" : "nieaktywny";
            $activatedAt = $profile->getActivatedAt()->toLocalizedString(SystemDateTimeFormat::dateOnly);
            $deactivatedAt = $profile->getDeactivatedAt()?->toLocalizedString(SystemDateTimeFormat::dateOnly);

?>
            <tr>
                <td><span class="status <?php echo $statusClass ?>"><?php echo $statusText ?></span></td>
                <td><?php echo $profile->getOwner()->getFormattedLoginAndUsername() ?></td>
                <td><?php echo $profile->getDirectorType() ?></td>
                <td class="optional"><?php echo $activatedAt ?></td>
<?php

                if (!$showingActiveOnly):

?>
                <td class="optional"><?php echo $deactivatedAt ?></td>
<?php

                endif;

?>
                <td class="action"><a href="<?php echo PathBuilder::action("/personnel/directors/profile/{$profile->getID()}") ?>">Pokaż szczegóły</a></td>
                <td class="summary">
                    <div class="statusContainer">
                        <span class="status <?php echo $statusClass ?>"><?php echo $statusText ?></span>
                    </div>
                    <?php echo $profile->getOwner()->getFormattedLoginAndUsername() ?><br>
                    <?php echo $directorType ?><br>
                    <a href="<?php echo PathBuilder::action("/personnel/directors/profile/{$profile->getID()}") ?>">Pokaż szczegóły</a>
                </td>
            </tr>
<?php

            endforeach;
            endif;

?>
        </table>
<?php

        if ($paginationInfo->getNumberOfPages() > 1):
        ViewBuilder::buildPagination($paginationInfo, $showingActiveOnly ? "/personnel/directors" : "/personnel/directors/all");
        endif;

?>
    </div>
</body>
</html>