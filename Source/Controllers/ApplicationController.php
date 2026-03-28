<?php

final class ApplicationController extends Controller {
    #[Route("/applications/new", RequestMethod::get)]
    #[Access(
        group: AccessGroup::guestsOnly
    )]
    public function showNewApplicationForm(): void {
        $parameters = [
            "login" => "",
            "username" => "",
            "day" => 0,
            "month" => 0,
            "year" => 0,
            "passedExamProofURL" => "",
            "motivation" => ""
        ];
        self::renderView(View::applicationNew, $parameters);
    }
}

?>