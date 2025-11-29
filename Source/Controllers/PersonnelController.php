<?php

final class PersonnelController extends Controller {
    #[Route("/personnel", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showActivePersonnelProfilesList(): void {
        $viewParameters = [
            "profiles" => Profile::getActiveByType(ProfileType::personnel, "(deactivated_at IS NULL) DESC, activated_at DESC"),
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
        $viewParameters = [
            "profiles" => Profile::getAllByType(ProfileType::personnel, "(deactivated_at IS NULL) DESC, activated_at DESC"),
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
        $viewParameters = [
            "profiles" => Profile::getActiveByType(ProfileType::director, "(deactivated_at IS NULL) DESC, activated_at DESC"),
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
        $viewParameters = [
            "profiles" => Profile::getAllByType(ProfileType::director, "(deactivated_at IS NULL) DESC, activated_at DESC"),
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
}

?>