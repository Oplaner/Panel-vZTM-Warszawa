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
}

?>