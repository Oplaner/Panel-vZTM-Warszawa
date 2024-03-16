<?php

require_once __DIR__."/Models/Classes/Router.php";
require_once __DIR__."/Models/Enums/RequestMethod.php";

$router = new Router((basename(__DIR__)));
$path = $_SERVER["REQUEST_URI"];
$method = RequestMethod::fromString($_SERVER["REQUEST_METHOD"]);
$router->dispatchRequest($path, $method);

?>