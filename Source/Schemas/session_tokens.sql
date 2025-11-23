CREATE TABLE `session_tokens` (
  `token` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `session_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `agent_hash` char(32) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `session_id_refreshed_at` datetime(6) NOT NULL,
  `valid_to` datetime(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `session_tokens`
  ADD PRIMARY KEY (`token`),
  ADD KEY `valid_to_key` (`valid_to`);