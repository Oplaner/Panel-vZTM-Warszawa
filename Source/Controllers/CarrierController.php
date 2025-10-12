<?php

final class CarrierController extends Controller {
    private const FULL_NAME_FIELD_NAME = "Nazwa pełna";
    private const SHORT_NAME_FIELD_NAME = "Nazwa skrócona";
    private const NUMBER_OF_TRIAL_TASKS_FIELD_NAME = "Liczba zadań do wykonania w trakcie okresu próbnego";
    private const NUMBER_OF_PENALTY_TASKS_FIELD_NAME = "Liczba zadań do wykonania w trakcie okresu karnego";
    private const SUPERVISORS_FIELD_NAME = "Kierownicy";

    #[Route("/carriers", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function showAllCarriersList(): void {
        $viewParameters = [
            "carriers" => Carrier::getAll("created_at", "DESC")
        ];
        self::renderView(View::carriers, $viewParameters);
    }

    #[Route("/carriers/active", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function showActiveCarriersList(): void {
        $viewParameters = [
            "carriers" => Carrier::getAllActive("created_at", "DESC"),
            "showingActiveOnly" => true
        ];
        self::renderView(View::carriers, $viewParameters);
    }

    #[Route("/carriers/new", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function showNewCarrierForm(): void {
        $viewParameters = [
            "fullName" => "",
            "shortName" => "",
            "numberOfTrialTasks" => "",
            "numberOfPenaltyTasks" => "",
            "supervisorSelections" => [],
            "supervisorLoginsString" => ""
        ];
        self::renderView(View::carrierNew, $viewParameters);
    }

    #[Route("/carriers/new", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function addNewCarrier(array $input): void {
        global $_USER;

        $post = $input[Router::POST_DATA_KEY];
        $fullName = InputValidator::clean($post["fullName"]);
        $shortName = InputValidator::clean($post["shortName"]);
        $numberOfTrialTasks = InputValidator::clean($post["numberOfTrialTasks"]);
        $numberOfPenaltyTasks = InputValidator::clean($post["numberOfPenaltyTasks"]);
        $supervisorSelections = [];
        $supervisorLoginsString = InputValidator::clean($post["supervisorLoginsString"]);
        $supervisorLogins = [];

        $showMessage = false;
        $messageType = null;
        $message = null;
        $isValidationSuccessful = true;

        try {
            InputValidator::checkNonEmpty(self::FULL_NAME_FIELD_NAME, $fullName);
            InputValidator::checkNonEmpty(self::SHORT_NAME_FIELD_NAME, $shortName);
            InputValidator::checkNonEmpty(self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME, $numberOfTrialTasks);
            InputValidator::checkNonEmpty(self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME, $numberOfPenaltyTasks);
            InputValidator::checkLength(self::FULL_NAME_FIELD_NAME, $fullName, 1, 30);
            InputValidator::checkLength(self::SHORT_NAME_FIELD_NAME, $shortName, 1, 10);
            InputValidator::checkInteger(self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME, $numberOfTrialTasks, 0, 255);
            InputValidator::checkInteger(self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME, $numberOfPenaltyTasks, 0, 255);

            if ($supervisorLoginsString != "") {
                $supervisorLogins = explode(";", $supervisorLoginsString);

                foreach ($supervisorLogins as $supervisorLogin) {
                    try {
                        InputValidator::checkInteger(self::SUPERVISORS_FIELD_NAME, $supervisorLogin, 0, 4294967295);
                    } catch (ValidationException) {
                        $supervisorLoginsString = "";
                        throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::SUPERVISORS_FIELD_NAME));
                    }
                }

                $supervisorSelections = User::getLoginAndUsernamePairsForAnyUsersWithLogins($supervisorLogins);
                $supervisorLogins = array_map(
                    fn($supervisorSelection) => $supervisorSelection["key"],
                    $supervisorSelections
                );
                $supervisorLoginsString = implode(";", $supervisorLogins);
            }
        } catch (ValidationException $exception) {
            $showMessage = true;
            $messageType = "error";
            $message = $exception->getMessage();
            $isValidationSuccessful = false;
        }

        if ($isValidationSuccessful) {
            $supervisors = [];

            foreach ($supervisorLogins as $supervisorLogin) {
                $supervisor = User::withLogin($supervisorLogin);

                if (is_null($supervisor)) {
                    $supervisor = User::createNew($supervisorLogin);
                    // TODO: Handle newly created User (myBB PM?).
                }

                $supervisors[] = $supervisor;
            }
    
            $carrier = Carrier::createNew($fullName, $shortName, $supervisors, $numberOfTrialTasks, $numberOfPenaltyTasks, $_USER);
            $showMessage = true;
            $messageType = "success";
            $message = "<span>Przewoźnik został utworzony! Możesz podejrzeć jego szczegóły, <a href=\"".PathBuilder::action("/carriers/{$carrier->getID()}")."\">klikając tutaj</a>.</span>";
            $fullName = "";
            $shortName = "";
            $numberOfTrialTasks = "";
            $numberOfPenaltyTasks = "";
            $supervisorSelections = [];
            $supervisorLoginsString = "";
        }

        $viewParameters = [
            "showMessage" => $showMessage,
            "messageType" => $messageType,
            "message" => $message,
            "fullName" => $fullName,
            "shortName" => $shortName,
            "numberOfTrialTasks" => $numberOfTrialTasks,
            "numberOfPenaltyTasks" => $numberOfPenaltyTasks,
            "supervisorSelections" => $supervisorSelections,
            "supervisorLoginsString" => $supervisorLoginsString
        ];
        self::renderView(View::carrierNew, $viewParameters);
    }

    #[Route("/carriers/{carrierID}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showCarrierDetails(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier)) {
            Router::redirect("/carriers");
        }

        $viewParameters = [
            "carrier" => $carrier
        ];
        self::renderView(View::carrierDetails, $viewParameters);
    }

    #[Route("/carriers/{carrierID}/edit", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showEditCarrierForm(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier)) {
            Router::redirect("/carriers");
        }

        $supervisors = $carrier->getSupervisors();
        usort($supervisors, fn($a, $b) => $a->getLogin() <=> $b->getLogin());
        $supervisorSelections = array_map(
            fn($supervisor) => [
                "key" => $supervisor->getLogin(),
                "value" => $supervisor->getFormattedLoginAndUsername()
            ],
            $supervisors
        );
        $supervisorLoginsString = implode(
            ";",
            array_map(
                fn($supervisorSelection) => $supervisorSelection["key"],
                $supervisorSelections
            )
        );
        $viewParameters = [
            "carrier" => $carrier,
            "fullName" => $carrier->getFullName(),
            "shortName" => $carrier->getShortName(),
            "numberOfTrialTasks" => $carrier->getNumberOfTrialTasks(),
            "numberOfPenaltyTasks" => $carrier->getNumberOfPenaltyTasks(),
            "supervisorSelections" => $supervisorSelections,
            "supervisorLoginsString" => $supervisorLoginsString
        ];
        self::renderView(View::carrierEdit, $viewParameters);
    }

    #[Route("/carriers/{carrierID}/edit", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function editCarrier(array $input): void {
        global $_USER;

        extract($input[Router::PATH_DATA_KEY]);
        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier)) {
            Router::redirect("/carriers");
        }

        $post = $input[Router::POST_DATA_KEY];
        $fullName = InputValidator::clean($post["fullName"]);
        $shortName = InputValidator::clean($post["shortName"]);
        $numberOfTrialTasks = InputValidator::clean($post["numberOfTrialTasks"]);
        $numberOfPenaltyTasks = InputValidator::clean($post["numberOfPenaltyTasks"]);
        $supervisorSelections = [];
        $supervisorLoginsString = InputValidator::clean($post["supervisorLoginsString"]);
        $supervisorLogins = [];

        $message = null;
        $isValidationSuccessful = true;

        try {
            InputValidator::checkNonEmpty(self::FULL_NAME_FIELD_NAME, $fullName);
            InputValidator::checkNonEmpty(self::SHORT_NAME_FIELD_NAME, $shortName);
            InputValidator::checkNonEmpty(self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME, $numberOfTrialTasks);
            InputValidator::checkNonEmpty(self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME, $numberOfPenaltyTasks);
            InputValidator::checkLength(self::FULL_NAME_FIELD_NAME, $fullName, 1, 30);
            InputValidator::checkLength(self::SHORT_NAME_FIELD_NAME, $shortName, 1, 10);
            InputValidator::checkInteger(self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME, $numberOfTrialTasks, 0, 255);
            InputValidator::checkInteger(self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME, $numberOfPenaltyTasks, 0, 255);

            if ($supervisorLoginsString != "") {
                $supervisorLogins = explode(";", $supervisorLoginsString);

                foreach ($supervisorLogins as $supervisorLogin) {
                    try {
                        InputValidator::checkInteger(self::SUPERVISORS_FIELD_NAME, $supervisorLogin, 0, 4294967295);
                    } catch (ValidationException) {
                        $supervisorLoginsString = "";
                        throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::SUPERVISORS_FIELD_NAME));
                    }
                }

                $supervisorSelections = User::getLoginAndUsernamePairsForAnyUsersWithLogins($supervisorLogins);
                $supervisorLogins = array_map(
                    fn($supervisorSelection) => $supervisorSelection["key"],
                    $supervisorSelections
                );
                $supervisorLoginsString = implode(";", $supervisorLogins);
            }
        } catch (ValidationException $exception) {
            $message = $exception->getMessage();
            $isValidationSuccessful = false;
        }

        if ($isValidationSuccessful) {
            $currentSupervisorLogins = array_map(
                fn($supervisor) => $supervisor->getLogin(),
                $carrier->getSupervisors()
            );
            $supervisorLoginsToAdd = [];
            $supervisorLoginsToRemove = [];

            foreach ($supervisorLogins as $supervisorLogin) {
                if (!in_array($supervisorLogin, $currentSupervisorLogins)) {
                    $supervisorLoginsToAdd[] = $supervisorLogin;
                }
            }

            foreach ($currentSupervisorLogins as $supervisorLogin) {
                if (!in_array($supervisorLogin, $supervisorLogins)) {
                    $supervisorLoginsToRemove[] = $supervisorLogin;
                }
            }

            $carrier->setFullName($fullName);
            $carrier->setShortName($shortName);
            $carrier->setNumberOfTrialTasks($numberOfTrialTasks);
            $carrier->setNumberOfPenaltyTasks($numberOfPenaltyTasks);

            foreach ($supervisorLoginsToAdd as $supervisorLogin) {
                $supervisor = User::withLogin($supervisorLogin);

                if (is_null($supervisor)) {
                    $supervisor = User::createNew($supervisorLogin);
                    // TODO: Handle newly created User (myBB PM?).
                }

                $carrier->addSupervisor($supervisor, $_USER);
            }

            foreach ($supervisorLoginsToRemove as $supervisorLogin) {
                $supervisor = User::withLogin($supervisorLogin);
                $carrier->removeSupervisor($supervisor, $_USER);
            }

            $viewParameters = [
                "carrier" => $carrier,
                "showMessage" => true,
                "messageType" => "success",
                "message" => "Przewoźnik został pomyślnie zaktualizowany."
            ];
            self::renderView(View::carrierDetails, $viewParameters);
        } else {
            $viewParameters = [
                "carrier" => $carrier,
                "showMessage" => true,
                "message" => $message,
                "fullName" => $fullName,
                "shortName" => $shortName,
                "numberOfTrialTasks" => $numberOfTrialTasks,
                "numberOfPenaltyTasks" => $numberOfPenaltyTasks,
                "supervisorSelections" => $supervisorSelections,
                "supervisorLoginsString" => $supervisorLoginsString
            ];
            self::renderView(View::carrierEdit, $viewParameters);
        }
    }

    #[Route("/carriers/{carrierID}/close", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showCloseCarrierConfirmation(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier)) {
            Router::redirect("/carriers");
        }

        $viewParameters = [
            "pageSubtitle" => $carrier->getFullName(),
            "title" => $carrier->getFullName(),
            "backAction" => "/carriers",
            "formAction" => "/carriers/$carrierID/close",
            "confirmationMessage" => "Czy na pewno chcesz zamknąć zakład? Tej czynności nie można cofnąć.",
            "infoMessage" => "Wszystkie kontrakty z kierowcami muszą zostać uprzednio zakończone. Lista kierowników zakładu zostanie wyczyszczona.",
            "cancelAction" => "/carriers/$carrierID",
            "submitButton" => "Zamknij zakład"
        ];
        self::renderView(View::confirmation, $viewParameters);
    }
}

?>