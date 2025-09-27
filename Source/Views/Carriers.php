<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu, Script::redirect], "Przewoźnicy")

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1>Przewoźnicy</h1>
        <div class="toolbar">
            <div class="optionContainer">
<?php

                $checked = "";
                $redirectURL = PathBuilder::action("/carriers/active");

                if (isset($showingActiveOnly) && $showingActiveOnly) {
                    $checked = " checked";
                    $redirectURL = PathBuilder::action("/carriers");
                }

?>
                <input type="checkbox" id="showActiveCarriersOnly" data-redirect="<?php echo $redirectURL ?>"<?php echo $checked ?>>
                <label for="showActiveCarriersOnly">Pokaż tylko aktywnych</label>
            </div>
            <a href="#" class="button">Utwórz nowego</a>
        </div>
        <table>
            <tr>
                <th>Status</th>
                <th>Nazwa</th>
                <th>Kierownicy</th>
                <th class="optional">Liczba kierowców</th>
                <th class="optional">Data utworzenia</th>
                <th class="optional">Data zamknięcia</th>
                <th>&nbsp;</th>
                <th class="summary">&nbsp;</th>
            </tr>
<?php

            if (count($carriers) == 0):

?>
            <tr>
                <td colspan="8">Brak danych do wyświetlenia.</td>
            </tr>
<?php

            else:
            foreach ($carriers as $carrier):
            $statusClass = $carrier->isActive() ? "active" : "inactive";
            $statusText = $carrier->isActive() ? "aktywny" : "zamknięty";
            $supervisors = join(
                "<br>",
                array_map(
                    fn ($supervisor) => $supervisor->getUsername(),
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
                <td class="optional"><?php echo $closedAt ?></td>
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
    </div>
</body>
</html>