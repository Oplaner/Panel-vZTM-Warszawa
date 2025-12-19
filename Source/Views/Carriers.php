<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::redirect], "Zakłady")

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1>Zakłady</h1>
        <div class="toolbar">
            <div class="inputContainer">
<?php

                $checked = $showingActiveOnly ? " checked" : "";
                $redirectAction = $showingActiveOnly ? "/carriers/all" : "/carriers";

?>
                <input type="checkbox" id="showActiveCarriersOnly" data-redirect="<?php echo PathBuilder::action($redirectAction) ?>"<?php echo $checked ?>>
                <label for="showActiveCarriersOnly">Pokaż tylko aktywne</label>
            </div>
            <a href="<?php echo PathBuilder::action("/carriers/new") ?>" class="button">Utwórz nowy</a>
        </div>
        <table>
            <tr>
                <th>Status</th>
                <th>Nazwa</th>
                <th>Kierownicy</th>
                <th class="optional">Liczba kierowców</th>
                <th class="optional">Data utworzenia</th>
<?php

                if (!$showingActiveOnly):

?>
                <th class="optional">Data zamknięcia</th>
<?php

                endif;

?>
                <th>&nbsp;</th>
                <th class="summary">&nbsp;</th>
            </tr>
<?php

            if (count($carriers) == 0):
            $colspan = $showingActiveOnly ? 7 : 8;

?>
            <tr>
                <td colspan="<?php echo $colspan ?>">Brak danych do wyświetlenia.</td>
            </tr>
<?php

            else:
            foreach ($carriers as $carrier):
            $statusClass = $carrier->isActive() ? "active" : "inactive";
            $statusText = $carrier->isActive() ? "aktywny" : "zamknięty";
            $supervisors = implode(
                "<br>",
                array_map(
                    fn($supervisor) => $supervisor->getUsername(),
                    $carrier->getSupervisors()
                )
            );
            $createdAt = $carrier->getCreatedAt()->toLocalizedString(SystemDateTimeFormat::dateOnly);
            $closedAt = $carrier->getClosedAt()?->toLocalizedString(SystemDateTimeFormat::dateOnly);

?>
            <tr>
                <td><span class="status <?php echo $statusClass ?>"><?php echo $statusText ?></span></td>
                <td><?php echo $carrier->getFullName() ?></td>
                <td><?php echo $supervisors ?></td>
                <td class="optional"><?php echo count($carrier->getActiveContracts()) ?></td>
                <td class="optional"><?php echo $createdAt ?></td>
<?php

                if (!$showingActiveOnly):

?>
                <td class="optional"><?php echo $closedAt ?></td>
<?php

                endif;

?>
                <td class="action"><a href="<?php echo PathBuilder::action("/carriers/{$carrier->getID()}") ?>">Pokaż szczegóły</a></td>
                <td class="summary">
                    <div class="statusContainer">
                        <span class="status <?php echo $statusClass ?>"><?php echo $statusText ?></span>
                    </div>
                    <?php echo $carrier->getFullName() ?><br>
                    <a href="<?php echo PathBuilder::action("/carriers/{$carrier->getID()}") ?>">Pokaż szczegóły</a>
                </td>
            </tr>
<?php

            endforeach;
            endif;

?>
        </table>
<?php

        if ($paginationInfo->getNumberOfPages() > 1):
        ViewBuilder::buildPagination($paginationInfo, $showingActiveOnly ? "/carriers" : "/carriers/all");
        endif;

?>
    </div>
</body>
</html>