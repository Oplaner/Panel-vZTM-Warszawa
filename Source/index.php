<?php

require_once __DIR__."/Models/Classes/Autoloader.php";

Autoloader::scanSourceDirectory(basename(__DIR__));
Logger::log(LogLevel::info, "===== APPLICATION START =====");
$properties = PropertiesReader::getProperties("application");
error_reporting($properties["errorReportingEnabled"] ? E_ALL : 0);

try {
    $_USER = Authenticator::getUserFromSessionData();
    $router = new Router(basename(__DIR__));
    $path = $_SERVER["REQUEST_URI"];
    $method = RequestMethod::from($_SERVER["REQUEST_METHOD"]);
    $router->dispatchRequest($path, $method);
} catch (Exception $exception) {
    Logger::log(LogLevel::error, $exception->getMessage()."\n----- STACK TRACE -----\n".$exception->getTraceAsString());
    // TODO: Redirect to an error page using the Router.
} finally {
    DatabaseConnector::closeConnection();
}

Logger::log(LogLevel::info, "===== APPLICATION END =====");

?>