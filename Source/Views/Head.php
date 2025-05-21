<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo PathBuilder::stylesheet("{$style->value}.css") ?>">
<?php

    foreach ($scripts as $script):

?>
    <script src="<?php echo PathBuilder::script("{$script->value}.js") ?>"></script>
<?php

    endforeach;

?>
    <title><?php echo $pageTitle ?></title>
</head>