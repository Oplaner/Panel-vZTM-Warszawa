<?php

final class CarrierController extends Controller {
    #[Route("/carriers", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function carriersList(): void {
        self::renderView("Carriers");
    }
}

?>