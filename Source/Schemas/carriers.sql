CREATE TABLE `carriers` (
  `id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `full_name` varchar(30) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `trial_tasks` tinyint(3) UNSIGNED NOT NULL,
  `penalty_tasks` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `created_by_user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `closed_at` datetime(6) DEFAULT NULL,
  `closed_by_user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `carriers`
  ADD PRIMARY KEY (`id`);