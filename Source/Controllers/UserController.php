<?php

final class UserController extends Controller {
    #[Route("/users/search", RequestMethod::post)]
    #[Access(
        group: AccessGroup::anyProfile     
    )]
    public function handleUserSearch(array $input): void {
        $substringFieldName = "ID lub nazwa użytkownika";

        $post = $input[Router::POST_DATA_KEY];
        $substring = InputValidator::clean($post["substring"]);

        try {
            InputValidator::checkNonEmpty($substringFieldName, $substring);
            InputValidator::checkLength($substringFieldName, $substring, 3, 120);
        } catch (ValidationException) {
            http_response_code(400);
            return;
        }

        $users = User::getAllLoginAndUsernamePairsContainingSubstring($substring);
        self::renderJSON($users);
    }
}

?>