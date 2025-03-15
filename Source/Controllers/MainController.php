<?php

final class MainController extends Controller {
    #[Route("/", RequestMethod::get, DEFAULT_ROUTE)]
    #[Access(
        group: AccessGroup::everyone
    )]
    public function mainPage(): void {
        global $_USER;

        if (isset($_USER)) {
            self::renderView("MainPage");
        } else {
            self::renderView("LoginPage");
        }
    }

    /*
    #[Route("/timetable/{depotID}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [PersonnelProfile::class],
        allowedPersonnelPrivileges: [
            ["scope" => PrivilegeScope::canViewTimetableOfDepot, "entityKey" => "depotID"]
        ]
    )]
    public function loginPage(array $input): void {
        extract($input["pathData"]);
        $viewParameters = [
            "pageTitle" => $pageTitle,
            "user" => $user
        ];

        $this->renderView("MainPage", $viewParameters);
    }
    */

    #[Route("/login", RequestMethod::post)]
    #[Access(
        group: AccessGroup::guestsOnly
    )]
    public function login(array $input): void {
        $post = $input["postData"];
        $login = $post["login"];
        $password = $post["password"];
        // TODO: Sanitize user input.
        $authentication = Authenticator::authenticateUser($login, $password);

        if ($authentication == AuthenticationResult::success) {
            $this->mainPage();
            return;
        }

        $viewParameters = [
            "authenticationResult" => $authentication
        ];

        self::renderView("LoginPage", $viewParameters);
    }

    #[Route("/logout", RequestMethod::get)]
    #[Access(
        group: AccessGroup::anyProfile
    )]
    public function logout(): void {
        global $_USER;
        
        Authenticator::endUserSession();
        $viewParameters = [
            "showLogoutMessage" => true
        ];

        self::renderView("LoginPage", $viewParameters);
    }
}

?>