<?php

require_once __DIR__."/../Source/Models/Classes/Autoloader.php";

Autoloader::scanSourceAndTestsDirectory();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Panel vZTM Warszawa Tests</title>
    <style>
        * {
            background-color: seashell;
            font-family: "Verdana", sans-serif;
            font-size: 18px;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: cadetblue;
            color: antiquewhite;
            font-size: 30px;
            padding: 20px;
        }

        h2 {
            background-color: bisque;
            font-size: 22px;
            padding: 10px 20px;
        }

        ul {
            padding: 20px 60px 10px 60px;
        }

        li {
            padding: 0 0 10px 0;
        }

        ul li ul {
            padding: 5px 0 5px 30px;
        }

        ul li ul li {
            font-size: 14px;
            padding: 0;
        }

        span.method {
            font-style: italic;
        }

        span.passed, span.failed {
            border-radius: 5px;
            font-size: 16px;
            padding: 5px;
        }

        span.passed {
            background-color: yellowgreen;
        }

        span.failed {
            background-color: darkred;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Panel vZTM Warszawa Tests</h1>
<?php

$testClasses = array_map(
    fn ($file) => preg_replace("/^(\S+)\.php$/", "$1", $file),
    array_filter(
        array_diff(
            scandir(__DIR__),
            [".", "..", basename(__DIR__)]
        ),
        fn ($file) => preg_match("/^\S+Tests\.php$/", $file)
    )
);

foreach ($testClasses as $class):

?>
    <h2><?php echo $class ?></h2>
    <ul>
<?php

    foreach (get_class_methods($class) as $method):
        $result = $class::$method();

        if ($result === true):

?>
        <li><span class="method"><?php echo $method ?>()</span> &rarr; <span class="passed">PASSED</span></li>
<?php

        else:

?>
        <li><span class="method"><?php echo $method ?>()</span> &rarr; <span class="failed">FAILED</span>
            <ul>
                <li><?php echo $result ?></li>
            </ul>
        </li>
<?php

        endif;
    endforeach;

?>
    </ul>
<?php

endforeach;

?>
</body>
</html>