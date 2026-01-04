<?php

final class MessageSenderTests {
    public static function sendMessage(): bool|string {
        $login = TestHelpers::EXISTING_TEST_USER_LOGIN;
        $subject = "Important message";
        $message = "Hello!";
        $db = DatabaseConnector::shared();
        $userInfoQuery = $db->prepare(
            "SELECT totalpms, unreadpms
            FROM mybb18_users
            WHERE uid = ?"
        );
        $userInfoQuery->bind_param("i", $login);
        $messagesCountQuery = $db->prepare(
            "SELECT COUNT(*)
            FROM mybb18_privatemessages
            WHERE uid = ?"
        );
        $messagesCountQuery->bind_param("i", $login);

        $userInfoQuery->execute();
        $userInfoBefore = $userInfoQuery->get_result()->fetch_assoc();
        $messagesCountQuery->execute();
        $messagesCountBefore = $messagesCountQuery->get_result()->fetch_column();
        MessageSender::sendMessage($login, $subject, $message);
        $userInfoQuery->execute();
        $userInfoAfter = $userInfoQuery->get_result()->fetch_assoc();
        $messagesCountQuery->execute();
        $messagesCountAfter = $messagesCountQuery->get_result()->fetch_column();

        if ($userInfoAfter["totalpms"] != $userInfoBefore["totalpms"] + 1
        || $userInfoAfter["unreadpms"] != $userInfoBefore["unreadpms"] + 1) {
            return "User information related to private messages is incorrect.";
        } elseif ($messagesCountAfter != $messagesCountBefore + 1) {
            return "User's messages count is incorrect.";
        }

        return true;
    }
}

?>