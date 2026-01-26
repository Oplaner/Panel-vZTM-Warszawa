<?php

final class ApplicationController extends Controller {
    #[Route("/applications/new", RequestMethod::get)]
    #[Access(
        group: AccessGroup::guestsOnly
    )]
    public function showNewApplicationForm(): void {
        // TODO
    }
}

?>