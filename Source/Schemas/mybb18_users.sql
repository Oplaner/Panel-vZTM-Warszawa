CREATE TABLE `mybb18_users` (
  `uid` int(10) UNSIGNED NOT NULL,
  `username` varchar(120) NOT NULL DEFAULT '',
  `totalpms` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `unreadpms` int(10) UNSIGNED NOT NULL DEFAULT 0,
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `mybb18_users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username_key` (`username`);

ALTER TABLE `mybb18_users`
  MODIFY `uid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

INSERT INTO `mybb18_users` (`uid`, `username`, `totalpms`, `unreadpms`) VALUES (1387, 'Oplaner', 0, 0);