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
    public function showActiveCarriersList(): void {
        $this->showActiveCarriersListByPage(self::makeFirstPageInputArray());
    }

    #[Route("/carriers/page/{pageNumber}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showActiveCarriersListByPage(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $pageNumber = InputValidator::clean($pageNumber);
        $paginationInfo = new PaginationInfo(Carrier::getActiveCount(), self::getNumberOfObjectsPerPage());

        try {
            InputValidator::checkInteger($pageNumber, 1, $paginationInfo->getNumberOfPages());
        } catch (ValidationException) {
            Router::redirect("/carriers");
        }

        $paginationInfo->setCurrentPage($pageNumber);
        $limitSubstring = $paginationInfo->getQueryLimitSubstring();
        $viewParameters = [
            "carriers" => Carrier::getActive("created_at DESC", $limitSubstring),
            "paginationInfo" => $paginationInfo,
            "showingActiveOnly" => true
        ];
        self::renderView(View::carriers, $viewParameters);
    }

    #[Route("/carriers/all", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showAllCarriersList(): void {
        $this->showAllCarriersListByPage(self::makeFirstPageInputArray());
    }

    #[Route("/carriers/all/page/{pageNumber}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showAllCarriersListByPage(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $pageNumber = InputValidator::clean($pageNumber);
        $paginationInfo = new PaginationInfo(Carrier::getAllCount(), self::getNumberOfObjectsPerPage());

        try {
            InputValidator::checkInteger($pageNumber, 1, $paginationInfo->getNumberOfPages());
        } catch (ValidationException) {
            Router::redirect("/carriers/all");
        }

        $paginationInfo->setCurrentPage($pageNumber);
        $limitSubstring = $paginationInfo->getQueryLimitSubstring();
        $viewParameters = [
            "carriers" => Carrier::getAll("(closed_at IS NULL) DESC, created_at DESC", $limitSubstring),
            "paginationInfo" => $paginationInfo,
            "showingActiveOnly" => false
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
        $supervisorLoginsString = InputValidator::clean($post["supervisorLoginsString"]);
        $supervisorSelections = [];
        $supervisorLogins = [];

        $errors = [];
        $messageType = null;
        $message = null;

        // Validation: full name.
        try {
            InputValidator::checkNonEmpty($fullName, self::FULL_NAME_FIELD_NAME);
            InputValidator::checkLength($fullName, 5, 30, self::FULL_NAME_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: short name.
        try {
            InputValidator::checkNonEmpty($shortName, self::SHORT_NAME_FIELD_NAME);
            InputValidator::checkLength($shortName, 3, 10, self::SHORT_NAME_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: number of trial tasks.
        try {
            InputValidator::checkNonEmpty($numberOfTrialTasks, self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME);
            InputValidator::checkInteger($numberOfTrialTasks, 0, 255, self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: number of penalty tasks.
        try {
            InputValidator::checkNonEmpty($numberOfPenaltyTasks, self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME);
            InputValidator::checkInteger($numberOfPenaltyTasks, 0, 255, self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: supervisors.
        try {
            if ($supervisorLoginsString != "") {
                $supervisorLogins = explode(";", $supervisorLoginsString);

                foreach ($supervisorLogins as $supervisorLogin) {
                    try {
                        InputValidator::checkInteger($supervisorLogin, 0, 4294967295);
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
            $errors[] = $exception->getMessage();
        }

        if (count($errors) > 0) {
            $messageType = "error";
            $message = self::makeErrorMessage($errors);
        } else {
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
            $messageType = "success";
            $message = "<span>Zakład został utworzony! Możesz podejrzeć jego szczegóły, <a href=\"".PathBuilder::action("/carriers/{$carrier->getID()}")."\">klikając tutaj</a>.</span>";
            $fullName = "";
            $shortName = "";
            $numberOfTrialTasks = "";
            $numberOfPenaltyTasks = "";
            $supervisorLoginsString = "";
            $supervisorSelections = [];
        }

        $viewParameters = [
            "showMessage" => true,
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

        try {
            InputValidator::checkUUIDv4($carrierID);
        } catch (ValidationException) {
            Router::redirect("/carriers");
        }

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

        try {
            InputValidator::checkUUIDv4($carrierID);
        } catch (ValidationException) {
            Router::redirect("/carriers");
        }

        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier) || !$carrier->isActive()) {
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

        try {
            InputValidator::checkUUIDv4($carrierID);
        } catch (ValidationException) {
            Router::redirect("/carriers");
        }

        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier) || !$carrier->isActive()) {
            Router::redirect("/carriers");
        }

        $post = $input[Router::POST_DATA_KEY];
        $fullName = InputValidator::clean($post["fullName"]);
        $shortName = InputValidator::clean($post["shortName"]);
        $numberOfTrialTasks = InputValidator::clean($post["numberOfTrialTasks"]);
        $numberOfPenaltyTasks = InputValidator::clean($post["numberOfPenaltyTasks"]);
        $supervisorLoginsString = InputValidator::clean($post["supervisorLoginsString"]);
        $supervisorSelections = [];
        $supervisorLogins = [];

        $errors = [];

        // Validation: full name.
        try {
            InputValidator::checkNonEmpty($fullName, self::FULL_NAME_FIELD_NAME);
            InputValidator::checkLength($fullName, 5, 30, self::FULL_NAME_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: short name.
        try {
            InputValidator::checkNonEmpty($shortName, self::SHORT_NAME_FIELD_NAME);
            InputValidator::checkLength($shortName, 3, 10, self::SHORT_NAME_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: number of trial tasks.
        try {
            InputValidator::checkNonEmpty($numberOfTrialTasks, self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME);
            InputValidator::checkInteger($numberOfTrialTasks, 0, 255, self::NUMBER_OF_TRIAL_TASKS_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: number of penalty tasks.
        try {
            InputValidator::checkNonEmpty($numberOfPenaltyTasks, self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME);
            InputValidator::checkInteger($numberOfPenaltyTasks, 0, 255, self::NUMBER_OF_PENALTY_TASKS_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: supervisors.
        try {
            if ($supervisorLoginsString != "") {
                $supervisorLogins = explode(";", $supervisorLoginsString);

                foreach ($supervisorLogins as $supervisorLogin) {
                    try {
                        InputValidator::checkInteger($supervisorLogin, 0, 4294967295);
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
            $errors[] = $exception->getMessage();
        }

        if (count($errors) > 0) {
            $viewParameters = [
                "carrier" => $carrier,
                "showMessage" => true,
                "message" => self::makeErrorMessage($errors),
                "fullName" => $fullName,
                "shortName" => $shortName,
                "numberOfTrialTasks" => $numberOfTrialTasks,
                "numberOfPenaltyTasks" => $numberOfPenaltyTasks,
                "supervisorSelections" => $supervisorSelections,
                "supervisorLoginsString" => $supervisorLoginsString
            ];
            self::renderView(View::carrierEdit, $viewParameters);
        } else {
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
                "message" => "Zakład został pomyślnie zaktualizowany."
            ];
            self::renderView(View::carrierDetails, $viewParameters);
        }
    }

    #[Route("/carriers/{carrierID}/close", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showCloseCarrierConfirmation(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($carrierID);
        } catch (ValidationException) {
            Router::redirect("/carriers");
        }

        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier) || !$carrier->isActive()) {
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

    #[Route("/carriers/{carrierID}/close", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function closeCarrier(array $input): void {
        global $_USER;
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($carrierID);
        } catch (ValidationException) {
            Router::redirect("/carriers");
        }

        $carrier = Carrier::withID($carrierID);
        $post = $input[Router::POST_DATA_KEY];

        if (is_null($carrier) || !$carrier->isActive() || !isset($post["confirmed"])) {
            Router::redirect("/carriers");
        }

        $messageType = null;
        $message = null;

        try {
            $carrier->close($_USER);
            $messageType = "success";
            $message = "Zakład został zamknięty.";
        } catch (DomainException) {
            $messageType = "error";
            $message = "Warunki konieczne do zamknięcia zakładu nie zostały spełnione. Wszystkie kontrakty z kierowcami muszą zostać uprzednio zakończone.";
        }

        $viewParameters = [
            "carrier" => $carrier,
            "showMessage" => true,
            "messageType" => $messageType,
            "message" => $message
        ];
        self::renderView(View::carrierDetails, $viewParameters);
    }
}

?>