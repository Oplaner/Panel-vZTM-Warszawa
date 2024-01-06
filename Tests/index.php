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

    const EXCLUDED_DIRECTORY_ELEMENTS = [".", "..", "index.php", "TestClass.php"];
    const TEST_FILE_PATTERN = "/^\S+Tests\.php$/";

    $testClasses = array_map(
        fn ($file) => preg_replace("/^(\S+)\.php$/", "$1", $file),
        array_filter(
            array_diff(
                scandir(__DIR__),
                EXCLUDED_DIRECTORY_ELEMENTS
            ),
            fn ($file) => preg_match(TEST_FILE_PATTERN, $file)
        )
    );

    foreach ($testClasses as $class) {
        eval("require(\"$class.php\");");
        echo "<h2>$class</h2>";
        echo "\r\n\t<ul>";

        foreach (get_class_methods($class) as $method) {
            eval("\$result = $class::$method();");
            echo "\r\n\t\t<li><span class=\"method\">$method()</span> &rarr; ";
            
            if ($result === true) {
                echo "<span class=\"passed\">PASSED</span>";
            } else {
                echo "<span class=\"failed\">FAILED</span>";
                echo "\r\n\t\t\t<ul>";
                echo "\r\n\t\t\t\t<li>$result</li>";
                echo "\r\n\t\t\t</ul>";
                echo "\r\n\t\t";
            }

            echo "</li>";
        }

        echo "\r\n\t</ul>\r\n";
    }

    ?>
</body>
</html>