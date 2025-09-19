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
        $login = InputValidator::clean($post["login"]);
        $password = InputValidator::clean($post["password"]);
        $authenticatorProperties = PropertiesReader::getProperties("authenticator");
        $authentication = AuthenticationResult::invalidCredentials;

        if (InputValidator::nonEmpty($login)
        && InputValidator::nonEmpty($password)
        && InputValidator::length($login, 1, 10)
        && InputValidator::length($password, $authenticatorProperties["minPasswordLength"], 255)) {
            $authentication = Authenticator::authenticateUser($login, $password);

            if ($authentication == AuthenticationResult::success) {
                Router::redirectToHome();
                return;
            }
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