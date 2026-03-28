<div class="datePicker">
    <select id="day" name="day">
<?php

        $selected = $day == 0 ? " selected" : "";

?>
        <option disabled<?php echo $selected ?>>Dzień</option>
<?php

        for ($i = 1; $i <= 31; $i++):
        $selected = $i == $day ? " selected" : "";

?>
        <option value="<?php echo $i ?>"<?php echo $selected ?>><?php echo $i ?></option>
<?php

        endfor;

?>
    </select>
    <select id="month" name="month">
<?php

        $selected = $month == 0 ? " selected" : "";

?>
        <option disabled<?php echo $selected ?>>Miesiąc</option>
<?php

        for ($i = 1; $i <= 12; $i++):
        $selected = $i == $month ? " selected" : "";
        $monthName = match ($i) {
            1 => "stycznia",
            2 => "lutego",
            3 => "marca",
            4 => "kwietnia",
            5 => "maja",
            6 => "czerwca",
            7 => "lipca",
            8 => "sierpnia",
            9 => "września",
            10 => "października",
            11 => "listopada",
            12 => "grudnia"
        };

?>
        <option value="<?php echo $i ?>"<?php echo $selected ?>><?php echo $monthName ?></option>
<?php

        endfor;

?>
    </select>
    <select id="year" name="year">
<?php

        $selected = $year == 0 ? " selected" : "";

?>
        <option disabled<?php echo $selected ?>>Rok</option>
<?php

        for ($i = $maxYear; $i >= $minYear; $i--):
        $selected = $i == $year ? " selected" : "";

?>
        <option value="<?php echo $i ?>"<?php echo $selected ?>><?php echo $i ?></option>
<?php

        endfor;

?>
    </select>
</div>