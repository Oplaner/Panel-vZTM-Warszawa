<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu], null)

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1>Przewoźnicy</h1>
        <table>
            <tr>
                <th>Status</th>
                <th>Nazwa</th>
                <th>Kierownicy</th>
                <th>Liczba kierowców</th>
                <th>Data utworzenia</th>
                <th>Data zamknięcia</th>
                <th>&nbsp;</th>
            </tr>
<?php

            if (count($carriers) == 0):

?>
            <tr>
                <td colspan="6">Brak danych do wyświetlenia.</td>
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
                <td class="status">
                    <span class="<?php echo $statusClass ?>"><?php echo $statusText ?></span>
                </td>
                <td><?php echo $carrier->getFullName() ?></td>
                <td><?php echo $supervisors ?></td>
                <td><?php echo count($carrier->getActiveContracts()) ?></td>
                <td><?php echo $createdAt ?></td>
                <td><?php echo $closedAt ?></td>
                <td><a href="#">Zarządzaj</a></td>
            </tr>
<?php

            endforeach;
            endif;

?>
        </table>
    </div>
</body>
</html>