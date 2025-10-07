<?php

final class CarrierController extends Controller {
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

        $fullNameFieldName = "Nazwa pełna";
        $shortNameFieldName = "Nazwa skrócona";
        $numberOfTrialTasksFieldName = "Liczba zadań do wykonania w trakcie okresu próbnego";
        $numberOfPenaltyTasksFieldName = "Liczba zadań do wykonania w trakcie okresu karnego";
        $supervisorsFieldName = "Kierownicy";

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
            InputValidator::checkNonEmpty($fullNameFieldName, $fullName);
            InputValidator::checkNonEmpty($shortNameFieldName, $shortName);
            InputValidator::checkNonEmpty($numberOfTrialTasksFieldName, $numberOfTrialTasks);
            InputValidator::checkNonEmpty($numberOfPenaltyTasksFieldName, $numberOfPenaltyTasks);
            InputValidator::checkLength($fullNameFieldName, $fullName, 1, 30);
            InputValidator::checkLength($shortNameFieldName, $shortName, 1, 10);
            InputValidator::checkInteger($numberOfTrialTasksFieldName, $numberOfTrialTasks, 0, 255);
            InputValidator::checkInteger($numberOfPenaltyTasksFieldName, $numberOfPenaltyTasks, 0, 255);

            if ($supervisorLoginsString != "") {
                $supervisorLogins = explode(";", $supervisorLoginsString);

                foreach ($supervisorLogins as $supervisorLogin) {
                    try {
                        InputValidator::checkInteger($supervisorsFieldName, $supervisorLogin, 0, 4294967295);
                    } catch (ValidationException) {
                        $supervisorLoginsString = "";
                        throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, $supervisorsFieldName));
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

            if (is_null($carrier)) {
                $errorID = DatabaseEntity::generateUUIDv4();
                Logger::log(LogLevel::error, "Failed to create Carrier: $carrier.", $errorID);
                $messageType = "error";
                $message = "Podczas tworzenia przewoźnika pojawił się problem. Przekaż administratorowi kod błędu: \"$errorID\" i spróbuj ponownie.";
            } else {
                $messageType = "success";
                $message = "<span>Przewoźnik został utworzony! Możesz podejrzeć jego szczegóły, <a href=\"".PathBuilder::action("/carriers/{$carrier->getID()}")."\">klikając tutaj</a>.</span>";
                $fullName = "";
                $shortName = "";
                $numberOfTrialTasks = "";
                $numberOfPenaltyTasks = "";
                $supervisorSelections = [];
                $supervisorLoginsString = "";
            }
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
}

?>