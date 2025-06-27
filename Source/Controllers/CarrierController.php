<?php

final class CarrierController extends Controller {
    #[Route("/carriers", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function carriersListAll(): void {
        $viewParameters = [
            "carriers" => Carrier::getAll()
        ];
        self::renderView("Carriers", $viewParameters);
    }

    #[Route("/carriers/active", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function carriersListAllActive(): void {
        $viewParameters = [
            "carriers" => Carrier::getAllActive(),
            "showingActiveOnly" => true
        ];
        self::renderView("Carriers", $viewParameters);
    }
}

?>