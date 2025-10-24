<?php

final class PersonnelController extends Controller {
    #[Route("/personnel", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function showUsersWithPersonnelProfileList(): void {
        self::renderView(View::personnel);
    }
}

?>