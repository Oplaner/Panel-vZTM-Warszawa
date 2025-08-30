<?php

final class CarrierController extends Controller {
    #[Route("/carriers", RequestMethod::get)]
    #[Access(
        group: AccessGroup::oneOfProfiles,
        profiles: [DirectorProfile::class]        
    )]
    public function showAllCarriersList(): void {
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
    public function showActiveCarriersList(): void {
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
    public function showCarrierDetails(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier) || !$carrier->isActive()) {
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
    public function showCarrierEdit(array $input): void {
        extract($input[Router::PATH_DATA_KEY]);
        $carrier = Carrier::withID($carrierID);

        if (is_null($carrier)) {
            Router::redirect("/carriers");
        }

        $viewParameters = [
            "carrier" => $carrier
        ];
        self::renderView(View::carrierEdit, $viewParameters);
    }
}

?>