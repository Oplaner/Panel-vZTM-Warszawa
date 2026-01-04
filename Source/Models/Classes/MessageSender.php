<?php

final class MessageSender {
    private const VZTM_USER_LOGIN = 589;
    private const INBOX_FOLDER_ID = 1;
    private const ATTENTION_ICON_ID = 2;
    private const UNREAD_STATUS_ID = 0;
    private const INCLUDE_SIGNATURE = true;
    private const ENABLE_EMOJIS = true;
    private const RECEIPT_NOT_REQUIRED_MODE_ID = 0;

    public static function sendMessage(int $login, string $subject, string $message): void {
        Logger::log(LogLevel::info, "Sending message with subject \"$subject\" to myBB user with ID $login.");
        $db = DatabaseConnector::shared();
        $db->execute_query(
            "INSERT INTO mybb18_privatemessages
            (pmid, uid, toid, fromid, recipients, folder, subject, icon, message, dateline, deletetime, status, statustime, includesig, smilieoff, receipt, readtime, ipaddress)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                null,
                $login,
                $login,
                self::VZTM_USER_LOGIN,
                self::makeRecipientsString($login),
                self::INBOX_FOLDER_ID,
                $subject,
                self::ATTENTION_ICON_ID,
                $message,
                SystemDateTime::now()->toTimestamp(),
                0,
                self::UNREAD_STATUS_ID,
                0,
                (int) self::INCLUDE_SIGNATURE,
                (int) !self::ENABLE_EMOJIS,
                self::RECEIPT_NOT_REQUIRED_MODE_ID,
                0,
                ""
            ]
        );
        $db->execute_query(
            "UPDATE mybb18_users
            SET totalpms = totalpms + 1, unreadpms = unreadpms + 1
            WHERE uid = ?",
            [
                $login
            ]
        );
    }

    private static function makeRecipientsString(int $toRecipient): string {
        $recipients = [
            "to" => [
                0 => "$toRecipient"
            ]
        ];
        return serialize($recipients);
    }
}

?>