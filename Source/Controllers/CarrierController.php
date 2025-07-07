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
        self::renderView(View::carriers, $viewParameters);
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
        self::renderView(View::carriers, $viewParameters);
    }

    #[Route("/carriers/{carrierID}", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]
    )]
    public function carrierDetails(array $input): void {
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
}

?>