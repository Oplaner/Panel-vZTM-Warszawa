CREATE TABLE `mybb18_privatemessages` (
  `pmid` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `toid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fromid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `recipients` text NOT NULL,
  `folder` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `subject` varchar(120) NOT NULL DEFAULT '',
  `icon` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `deletetime` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `statustime` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `includesig` tinyint(1) NOT NULL DEFAULT 0,
  `smilieoff` tinyint(1) NOT NULL DEFAULT 0,
  `receipt` tinyint(1) NOT NULL DEFAULT 0,
  `readtime` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ipaddress` varbinary(16) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `mybb18_privatemessages`
  ADD PRIMARY KEY (`pmid`),
  ADD KEY `uid` (`uid`,`folder`),
  ADD KEY `toid` (`toid`);

ALTER TABLE `mybb18_privatemessages`
  MODIFY `pmid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;