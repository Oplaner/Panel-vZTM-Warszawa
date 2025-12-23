<?php

final class UserController extends Controller {
    #[Route("/users/search/all", RequestMethod::post)]
    #[Access(
        group: AccessGroup::anyProfile     
    )]
    public function handleAllUserSearch(array $input): void {
        $searchMethod = fn($substring) => User::getAllLoginAndUsernamePairsContainingSubstring($substring);
        self::handleUserSearch($input, $searchMethod);
    }

    #[Route("/users/search/non-personnel", RequestMethod::post)]
    #[Access(
        group: AccessGroup::anyProfile     
    )]
    public function handleNonPersonnelUserSearch(array $input): void {
        $searchMethod = fn($substring) => User::getNonProfileTypeLoginAndUsernamePairsContainingSubstring(ProfileType::personnel, $substring);
        self::handleUserSearch($input, $searchMethod);
    }

    #[Route("/users/search/non-director", RequestMethod::post)]
    #[Access(
        group: AccessGroup::anyProfile     
    )]
    public function handleNonDirectorUserSearch(array $input): void {
        $searchMethod = fn($substring) => User::getNonProfileTypeLoginAndUsernamePairsContainingSubstring(ProfileType::director, $substring);
        self::handleUserSearch($input, $searchMethod);
    }

    private function handleUserSearch(array $input, Closure $searchMethod): void {
        $post = $input[Router::POST_DATA_KEY];
        $substring = InputValidator::clean($post["substring"]);

        try {
            InputValidator::checkNonEmpty($substring);
            InputValidator::checkLength($substring, 3, 120);
        } catch (ValidationException) {
            http_response_code(400);
            return;
        }

        $users = $searchMethod($substring);
        self::renderJSON($users);
    }
}

?>