<?php

require_once __DIR__."/Controller.php";
require_once __DIR__."/../Models/Classes/Route.php";
require_once __DIR__."/../Models/Enums/RequestMethod.php";

final class MainController extends Controller {
    #[Route("/", RequestMethod::get, DEFAULT_ROUTE)]
    public function mainPage(): void {
        $viewParameters = [
            "pageTitle" => "Main"
        ];
        $this->renderView("MainPage", $viewParameters);
    }

    #[Route("/title/{pageTitle}/user/{user}", RequestMethod::get)]
    public function loginPage(array $input): void {
        extract($input["pathData"]);
        $viewParameters = [
            "pageTitle" => $pageTitle,
            "user" => $user
        ];
        $this->renderView("MainPage", $viewParameters);
    }
}

?>