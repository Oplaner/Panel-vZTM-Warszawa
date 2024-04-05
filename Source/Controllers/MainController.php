<?php

require_once __DIR__."/Controller.php";
require_once __DIR__."/../Models/Classes/Authenticator.php";
require_once __DIR__."/../Models/Classes/Route.php";
require_once __DIR__."/../Models/Enums/AuthenticationResult.php";
require_once __DIR__."/../Models/Enums/RequestMethod.php";

final class MainController extends Controller {
    #[Route("/", RequestMethod::get, DEFAULT_ROUTE)]
    public function mainPage(): void {
        $this->renderView("MainPage");
    }

    /*
    #[Route("/title/{pageTitle}/user/{user}", RequestMethod::get)]
    public function loginPage(array $input): void {
        extract($input["pathData"]);
        $viewParameters = [
            "pageTitle" => $pageTitle,
            "user" => $user
        ];

        $this->renderView("MainPage", $viewParameters);
    }
    */

    #[Route("/", RequestMethod::post)]
    public function login(array $input): void {
        $post = $input["postData"];
        $login = $post["login"];
        $password = $post["password"];
        // TODO: Sanitize user input.
        $authentication = Authenticator::authenticateUser($login, $password);
        $viewParameters = [
            "authenticationResult" => $authentication
        ];

        $this->renderView("MainPage", $viewParameters);
    }

    #[Route("/logout", RequestMethod::get)]
    public function logout(): void {
        global $_USER;
        $didLogout = false;

        if (isset($_USER)) {
            Authenticator::endUserSession();
            $didLogout = true;
        }

        $viewParameters = [
            "showLogoutMessage" => $didLogout
        ];

        $this->renderView("MainPage", $viewParameters);
    }
}

?>