CREATE TABLE `mybb18_users` (
  `uid` int(10) UNSIGNED NOT NULL,
  `username` varchar(120) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `mybb18_users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `mybb18_users`
  MODIFY `uid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

INSERT INTO `mybb18_users` (`uid`, `username`) VALUES (1387, 'Oplaner');