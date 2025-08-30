<?php

final class MainController extends Controller {
    #[Route("/", RequestMethod::get)]
    #[Access(
        group: AccessGroup::everyone
    )]
    public function showMainPage(): void {
        global $_USER;

        if (isset($_USER)) {
            self::renderView(View::main);
        } else {
            self::renderView(View::login);
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
    public function handleLogin(array $input): void {
        $post = $input[Router::POST_DATA_KEY];
        $login = $post["login"];
        $password = $post["password"];
        // TODO: Sanitize user input.
        $authentication = Authenticator::authenticateUser($login, $password);

        if ($authentication == AuthenticationResult::success) {
            Router::redirectToHome();
            return;
        }

        $viewParameters = [
            "authenticationResult" => $authentication
        ];

        self::renderView(View::login, $viewParameters);
    }

    #[Route("/logout", RequestMethod::get)]
    #[Access(
        group: AccessGroup::anyProfile
    )]
    public function handleLogout(): void {
        Authenticator::endUserSession();
        $viewParameters = [
            "showLogoutMessage" => true
        ];

        self::renderView(View::login, $viewParameters);
    }
}

?>