<?php

final class PersonnelController extends Controller {
    private const PERSONNEL_LOGIN_FIELD_NAME = "Pracownik";
    private const PERSONNEL_DESCRIPTION_FIELD_NAME = "Opis funkcji";
    private const PERSONNEL_PRIVILEGES_FIELD_NAME = "Uprawnienia";

    #[Route("/personnel", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showActivePersonnelProfilesList(): void {
        $this->showActivePersonnelProfilesListByPage(self::makeFirstPageInputArray());
    }

    #[Route("/personnel/page/{pageNumber}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showActivePersonnelProfilesListByPage(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $pageNumber = InputValidator::clean($pageNumber);
        $paginationInfo = new PaginationInfo(Profile::getActiveCountByType(ProfileType::personnel), self::getNumberOfObjectsPerPage());

        try {
            InputValidator::checkInteger($pageNumber, 1, $paginationInfo->getNumberOfPages());
        } catch (ValidationException) {
            Router::redirect("/personnel");
        }

        $paginationInfo->setCurrentPage($pageNumber);
        $limitSubstring = $paginationInfo->getQueryLimitSubstring();
        $viewParameters = [
            "profiles" => Profile::getActiveByType(ProfileType::personnel, "(deactivated_at IS NULL) DESC, activated_at DESC", $limitSubstring),
            "paginationInfo" => $paginationInfo,
            "showingActiveOnly" => true
        ];
        self::renderView(View::personnelProfiles, $viewParameters);
    }

    #[Route("/personnel/all", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showAllPersonnelProfilesList(): void {
        $this->showAllPersonnelProfilesListByPage(self::makeFirstPageInputArray());
    }

    #[Route("/personnel/all/page/{pageNumber}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showAllPersonnelProfilesListByPage(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $pageNumber = InputValidator::clean($pageNumber);
        $paginationInfo = new PaginationInfo(Profile::getAllCountByType(ProfileType::personnel), self::getNumberOfObjectsPerPage());

        try {
            InputValidator::checkInteger($pageNumber, 1, $paginationInfo->getNumberOfPages());
        } catch (ValidationException) {
            Router::redirect("/personnel/all");
        }

        $paginationInfo->setCurrentPage($pageNumber);
        $limitSubstring = $paginationInfo->getQueryLimitSubstring();
        $viewParameters = [
            "profiles" => Profile::getAllByType(ProfileType::personnel, "(deactivated_at IS NULL) DESC, activated_at DESC", $limitSubstring),
            "paginationInfo" => $paginationInfo,
            "showingActiveOnly" => false
        ];
        self::renderView(View::personnelProfiles, $viewParameters);
    }

    #[Route("/personnel/directors", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showActiveDirectorProfilesList(): void {
        $this->showActiveDirectorProfilesListByPage(self::makeFirstPageInputArray());
    }

    #[Route("/personnel/directors/page/{pageNumber}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showActiveDirectorProfilesListByPage(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $pageNumber = InputValidator::clean($pageNumber);
        $paginationInfo = new PaginationInfo(Profile::getActiveCountByType(ProfileType::director), self::getNumberOfObjectsPerPage());

        try {
            InputValidator::checkInteger($pageNumber, 1, $paginationInfo->getNumberOfPages());
        } catch (ValidationException) {
            Router::redirect("/personnel/directors");
        }

        $paginationInfo->setCurrentPage($pageNumber);
        $limitSubstring = $paginationInfo->getQueryLimitSubstring();
        $viewParameters = [
            "profiles" => Profile::getActiveByType(ProfileType::director, "(deactivated_at IS NULL) DESC, activated_at DESC", $limitSubstring),
            "paginationInfo" => $paginationInfo,
            "showingActiveOnly" => true
        ];
        self::renderView(View::directorProfiles, $viewParameters);
    }

    #[Route("/personnel/directors/all", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showAllDirectorProfilesList(): void {
        $this->showAllDirectorProfilesListByPage(self::makeFirstPageInputArray());
    }

    #[Route("/personnel/directors/all/page/{pageNumber}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showAllDirectorProfilesListByPage(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $pageNumber = InputValidator::clean($pageNumber);
        $paginationInfo = new PaginationInfo(Profile::getAllCountByType(ProfileType::director), self::getNumberOfObjectsPerPage());

        try {
            InputValidator::checkInteger($pageNumber, 1, $paginationInfo->getNumberOfPages());
        } catch (ValidationException) {
            Router::redirect("/personnel/directors/all");
        }

        $paginationInfo->setCurrentPage($pageNumber);
        $limitSubstring = $paginationInfo->getQueryLimitSubstring();
        $viewParameters = [
            "profiles" => Profile::getAllByType(ProfileType::director, "(deactivated_at IS NULL) DESC, activated_at DESC", $limitSubstring),
            "paginationInfo" => $paginationInfo,
            "showingActiveOnly" => false
        ];
        self::renderView(View::directorProfiles, $viewParameters);
    }

    #[Route("/personnel/profile/{profileID}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showPersonnelProfileDetails(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel");
        }

        $profile = PersonnelProfile::withID($profileID);

        if (is_null($profile)) {
            Router::redirect("/personnel");
        }

        $viewParameters = [
            "profile" => $profile
        ];
        self::renderView(View::personnelProfileDetails, $viewParameters);
    }

    #[Route("/personnel/directors/profile/{profileID}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showDirectorProfileDetails(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel/directors");
        }

        $profile = DirectorProfile::withID($profileID);

        if (is_null($profile)) {
            Router::redirect("/personnel/directors");
        }

        $viewParameters = [
            "profile" => $profile
        ];
        self::renderView(View::directorProfileDetails, $viewParameters);
    }

    #[Route("/personnel/profile/{profileID}/deactivate", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showDeactivatePersonnelProfileConfirmation(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel");
        }

        $profile = PersonnelProfile::withID($profileID);

        if (is_null($profile) || !$profile->isActive()) {
            Router::redirect("/personnel");
        }

        $viewParameters = [
            "pageSubtitle" => $profile->getOwner()->getFormattedLoginAndUsername(),
            "title" => $profile->getOwner()->getFormattedLoginAndUsername(),
            "backAction" => "/personnel",
            "formAction" => "/personnel/profile/$profileID/deactivate",
            "confirmationMessage" => "Czy na pewno chcesz dezaktywować profil \"{$profile->getDescription()}\" tego pracownika? Tej czynności nie można cofnąć.",
            "infoMessage" => "<span>Jeśli jest to jedyny profil tego użytkownika, utraci on dostęp do systemu.<br>Usunięcie uprawnień kierownika zakładu nie spowoduje aktualizacji listy kierowników tego zakładu.</span>",
            "cancelAction" => "/personnel/profile/$profileID",
            "submitButton" => "Dezaktywuj profil"
        ];
        self::renderView(View::confirmation, $viewParameters);
    }

    #[Route("/personnel/profile/{profileID}/deactivate", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function deactivatePersonnelProfile(array $input): void {
        global $_USER;
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel");
        }

        $profile = PersonnelProfile::withID($profileID);
        $post = $input[Router::POST_DATA_KEY];

        if (is_null($profile) || !$profile->isActive() || !isset($post["confirmed"])) {
            Router::redirect("/personnel");
        }

        $profile->deactivate($_USER);
        $viewParameters = [
            "profile" => $profile,
            "showMessage" => true,
            "message" => "Profil został zdezaktywowany."
        ];
        self::renderView(View::personnelProfileDetails, $viewParameters);
    }

    #[Route("/personnel/directors/profile/{profileID}/deactivate", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showDeactivateDirectorProfileConfirmation(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel/directors");
        }

        $profile = DirectorProfile::withID($profileID);

        if (is_null($profile) || !$profile->isActive() || $profile->isProtected()) {
            Router::redirect("/personnel/directors");
        }

        $viewParameters = [
            "pageSubtitle" => $profile->getOwner()->getFormattedLoginAndUsername(),
            "title" => $profile->getOwner()->getFormattedLoginAndUsername(),
            "backAction" => "/personnel/directors",
            "formAction" => "/personnel/directors/profile/$profileID/deactivate",
            "confirmationMessage" => "Czy na pewno chcesz dezaktywować profil dyrektora? Tej czynności nie można cofnąć.",
            "infoMessage" => "Jeśli jest to jedyny profil tego użytkownika, utraci on dostęp do systemu.",
            "cancelAction" => "/personnel/directors/profile/$profileID",
            "submitButton" => "Dezaktywuj profil"
        ];
        self::renderView(View::confirmation, $viewParameters);
    }

    #[Route("/personnel/directors/profile/{profileID}/deactivate", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function deactivateDirectorProfile(array $input): void {
        global $_USER;
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel/directors");
        }

        $profile = DirectorProfile::withID($profileID);
        $post = $input[Router::POST_DATA_KEY];

        if (is_null($profile) || !$profile->isActive() || $profile->isProtected() || !isset($post["confirmed"])) {
            Router::redirect("/personnel/directors");
        }

        $profile->deactivate($_USER);
        $viewParameters = [
            "profile" => $profile,
            "showMessage" => true,
            "message" => "Profil został zdezaktywowany."
        ];
        self::renderView(View::directorProfileDetails, $viewParameters);
    }

    #[Route("/personnel/new-profile", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showNewPersonnelProfileForm(): void {
        $viewParameters = [
            "personnelSelection" => null,
            "personnelLogin" => "",
            "description" => "",
            "privilegeGroups" => Privilege::getGrantableGroups(),
            "privileges" => []
        ];
        self::renderView(View::personnelProfileNew, $viewParameters);
    }

    #[Route("/personnel/new-profile", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function addNewPersonnelProfile(array $input): void {
        global $_USER;

        $post = $input[Router::POST_DATA_KEY];
        $personnelLogin = InputValidator::clean($post["personnelLogin"]);
        $description = InputValidator::clean($post["description"]);
        $personnelSelection = null;
        $personnelUser = null;
        $privileges = [];

        $errors = [];
        $messageType = null;
        $message = null;

        Logger::log(LogLevel::info, "Validating new personnel profile information.");

        // Validation: personnel login.
        try {
            InputValidator::checkNonEmpty($personnelLogin, self::PERSONNEL_LOGIN_FIELD_NAME);
            InputValidator::checkInteger($personnelLogin, 0, 4294967295, self::PERSONNEL_LOGIN_FIELD_NAME);
            $personnelSelections = User::getLoginAndUsernamePairsForAnyUsersWithLogins([$personnelLogin]);

            if (empty($personnelSelections)) {
                throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::PERSONNEL_LOGIN_FIELD_NAME));
            }

            $personnelSelection = $personnelSelections[0];
            $personnelUser = User::withLogin($personnelLogin);

            if (!is_null($personnelUser) && $personnelUser->hasActiveProfileOfType(ProfileType::personnel)) {
                throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::PERSONNEL_LOGIN_FIELD_NAME));
            }
        } catch (ValidationException $exception) {
            $personnelLogin = "";
            $personnelSelection = null;
            $personnelUser = null;
            $errors[] = $exception->getMessage();
        }

        // Validation: description.
        try {
            InputValidator::checkNonEmpty($description, self::PERSONNEL_DESCRIPTION_FIELD_NAME);
            InputValidator::checkLength($description, 5, 100, self::PERSONNEL_DESCRIPTION_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: privileges.
        try {
            $key = "privileges";
            InputValidator::checkNonEmptyCheckboxArray($post, $key, self::PERSONNEL_PRIVILEGES_FIELD_NAME);

            foreach (array_keys($post[$key]) as $privilegeID) {
                InputValidator::checkUUIDv4($privilegeID);
                $privilege = Privilege::withID($privilegeID);

                if (is_null($privilege)) {
                    throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::PERSONNEL_PRIVILEGES_FIELD_NAME));
                }

                $privileges[] = $privilege;
            }
        } catch (ValidationException $exception) {
            $privileges = [];
            $errors[] = $exception->getMessage();
        }

        if (!empty($errors)) {
            Logger::log(LogLevel::info, "Validation failed.");
            $messageType = "error";
            $message = self::makeErrorMessage($errors);
        } else {
            Logger::log(LogLevel::info, "Validation succeeded.");

            if (is_null($personnelUser)) {
                $personnelUser = User::createNew($personnelLogin);
                // TODO: Handle newly created User (myBB PM?).
            }

            $profile = PersonnelProfile::createNew($personnelUser, $_USER, $description, $privileges);
            $messageType = "success";
            $message = "<span>Profil personelu został utworzony! Możesz podejrzeć jego szczegóły, <a href=\"".PathBuilder::action("/personnel/profile/{$profile->getID()}")."\">klikając tutaj</a>.</span>";
            $personnelLogin = "";
            $description = "";
            $personnelSelection = null;
            $privileges = [];
        }

        $viewParameters = [
            "showMessage" => true,
            "messageType" => $messageType,
            "message" => $message,
            "personnelSelection" => $personnelSelection,
            "personnelLogin" => $personnelLogin,
            "description" => $description,
            "privilegeGroups" => Privilege::getGrantableGroups(),
            "privileges" => $privileges
        ];
        self::renderView(View::personnelProfileNew, $viewParameters);
    }

    #[Route("/personnel/profile/{profileID}/edit", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showEditPersonnelProfileForm(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel");
        }

        $profile = PersonnelProfile::withID($profileID);

        if (is_null($profile) || !$profile->isActive()) {
            Router::redirect("/personnel");
        }

        $viewParameters = [
            "profile" => $profile,
            "description" => $profile->getDescription(),
            "privilegeGroups" => Privilege::getGrantableGroups(),
            "privileges" => $profile->getPrivileges()
        ];
        self::renderView(View::personnelProfileEdit, $viewParameters);
    }

    #[Route("/personnel/profile/{profileID}/edit", RequestMethod::post)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function editPersonnelProfile(array $input): void {
        global $_USER;
        extract($input[Router::PATH_DATA_KEY]);

        try {
            InputValidator::checkUUIDv4($profileID);
        } catch (ValidationException) {
            Router::redirect("/personnel");
        }

        $profile = PersonnelProfile::withID($profileID);

        if (is_null($profile) || !$profile->isActive()) {
            Router::redirect("/personnel");
        }

        $post = $input[Router::POST_DATA_KEY];
        $description = InputValidator::clean($post["description"]);
        $privileges = [];

        $errors = [];

        Logger::log(LogLevel::info, "Validating updated information for personnel profile with ID \"{$profile->getID()}\".");

        // Validation: description.
        try {
            InputValidator::checkNonEmpty($description, self::PERSONNEL_DESCRIPTION_FIELD_NAME);
            InputValidator::checkLength($description, 5, 100, self::PERSONNEL_DESCRIPTION_FIELD_NAME);
        } catch (ValidationException $exception) {
            $errors[] = $exception->getMessage();
        }

        // Validation: privileges.
        try {
            $key = "privileges";
            InputValidator::checkNonEmptyCheckboxArray($post, $key, self::PERSONNEL_PRIVILEGES_FIELD_NAME);

            foreach (array_keys($post[$key]) as $privilegeID) {
                InputValidator::checkUUIDv4($privilegeID);
                $privilege = Privilege::withID($privilegeID);

                if (is_null($privilege)) {
                    throw new ValidationException(InputValidator::generateErrorMessage(InputValidator::MESSAGE_TEMPLATE_GENERIC, self::PERSONNEL_PRIVILEGES_FIELD_NAME));
                }

                $privileges[] = $privilege;
            }
        } catch (ValidationException $exception) {
            $privileges = [];
            $errors[] = $exception->getMessage();
        }

        // Validation: difference check.
        $descriptionDidChange = $description != $profile->getDescription();
        $privilegeIDs = array_map(
            fn($privilege) => $privilege->getID(),
            $privileges
        );
        $profilePrivilegeIDs = array_map(
            fn($profilePrivilege) => $profilePrivilege->getID(),
            $profile->getPrivileges()
        );
        $privilegesDidChange = count($privilegeIDs) != count($profilePrivilegeIDs) || !empty(array_diff($privilegeIDs, $profilePrivilegeIDs));
        $profileDidChange = $descriptionDidChange || $privilegesDidChange;

        if (!empty($errors) || !$profileDidChange) {
            Logger::log(LogLevel::info, "Validation failed or the information did not change.");
            $viewParameters = [
                "profile" => $profile,
                "showMessage" => true,
                "message" => !empty($errors) ? self::makeErrorMessage($errors) : "Nie wprowadzono żadnych zmian. Profil nie został zaktualizowany.",
                "description" => $description,
                "privilegeGroups" => Privilege::getGrantableGroups(),
                "privileges" => $privileges
            ];
            self::renderView(View::personnelProfileEdit, $viewParameters);
        } else {
            Logger::log(LogLevel::info, "Validation succeeded.");
            $profile->deactivate($_USER);
            $newProfile = PersonnelProfile::createNew($profile->getOwner(), $_USER, $description, $privileges);
            $viewParameters = [
                "profile" => $profile,
                "showMessage" => true,
                "message" => "<span>Profil personelu został pomyślnie zaktualizowany. Aktualnie widzisz poprzedni, zdezaktywowany profil. Nowy profil możesz podejrzeć, <a href=\"".PathBuilder::action("/personnel/profile/{$newProfile->getID()}")."\">klikając tutaj</a>.</span>"
            ];
            self::renderView(View::personnelProfileDetails, $viewParameters);
        }
    }
}

?>