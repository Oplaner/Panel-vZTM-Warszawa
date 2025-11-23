CREATE TABLE `profiles` (
  `id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `type` varchar(40) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `activated_at` datetime(6) NOT NULL,
  `activated_by_user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `deactivated_at` datetime(6) DEFAULT NULL,
  `deactivated_by_user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_key` (`user_id`);