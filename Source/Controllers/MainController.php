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
        profiles: [ProfileType::personnel],
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
        $minPasswordLength = $authenticatorProperties["minPasswordLength"];
        $isValidationSuccessful = true;
        $authenticationResult = null;

        Logger::log(LogLevel::info, "Validating login request.");

        try {
            InputValidator::checkNonEmpty($login);
            InputValidator::checkNonEmpty($password);
            InputValidator::checkLength($login, 1, 10);
            InputValidator::checkLength($password, $minPasswordLength, 255);
        } catch (ValidationException) {
            Logger::log(LogLevel::info, "Validation failed.");
            $isValidationSuccessful = false;
            $authenticationResult = AuthenticationResult::invalidCredentials;
        }

        if ($isValidationSuccessful) {
            Logger::log(LogLevel::info, "Validation succeeded. Beginning authentication.");
            $authenticationResult = Authenticator::authenticateUser($login, $password);

            if ($authenticationResult == AuthenticationResult::success) {
                Router::redirectToHome();
                return;
            }
        }

        $viewParameters = [
            "authenticationResult" => $authenticationResult
        ];
        self::renderView(View::login, $viewParameters);
    }

    #[Route("/logout", RequestMethod::get)]
    #[Access(
        group: AccessGroup::anyProfile
    )]
    public function handleLogout(): void {
        Logger::log(LogLevel::info, "Handling logout request.");
        Authenticator::endUserSession();
        $viewParameters = [
            "showLogoutMessage" => true
        ];
        self::renderView(View::login, $viewParameters);
    }
}

?>