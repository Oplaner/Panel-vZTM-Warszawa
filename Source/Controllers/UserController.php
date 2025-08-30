<?php

final class UserController extends Controller {
    #[Route("/users/search", RequestMethod::post)]
    #[Access(
        group: AccessGroup::anyProfile     
    )]
    public function handleUserSearch(array $input): void {
        $post = $input[Router::POST_DATA_KEY];
        $substring = $post["substring"];

        // TODO: Add more validation.
        if (mb_strlen($substring) < 3) {
            http_response_code(400);
            return;
        }

        $users = User::getAllLoginAndUsernamePairsContaining($substring);
        self::renderJSON($users);
    }
}

?>