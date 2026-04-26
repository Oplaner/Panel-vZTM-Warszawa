<?php

$dataSelectionLimit = is_null($selectionLimit) ? "" : " data-selection-limit=\"$selectionLimit\"";

?>
<div class="searchContainer" data-source="<?php echo PathBuilder::action($searchAction) ?>"<?php echo $dataSelectionLimit ?>>
    <div class="selectionContainer">
<?php

        foreach ($selections as $selection):

?>
        <div class="selection" data-key="<?php echo $selection["key"] ?>">
            <span><?php echo $selection["value"] ?></span>&nbsp;<a href="#">[&times;]</a>
        </div>
<?php

        endforeach;

?>
    </div>
<?php

    $required = $inputRequired ? " class=\"required\"" : "";
    $disabled = isset($selectionLimit) && count($selections) == $selectionLimit ? " disabled" : "";

?>
    <input type="hidden" name="<?php echo $rawInputName ?>" value="<?php echo $rawInputValue ?>">
    <label for="<?php echo $inputID ?>"<?php echo $required ?>><?php echo $inputLabel ?></label>
    <div class="inputWithLoader">
        <input type="text" id="<?php echo $inputID ?>" placeholder="<?php echo $inputPlaceholder ?>" <?php echo $disabled ?>>
        <div class="loaderContainer">
            <div class="loader"></div>
        </div>
    </div>
    <div class="searchMatchesContainer">
        <div class="searchMatchesScrollContainer">
            <div class="searchMatches"></div>
        </div>
    </div>
</div>