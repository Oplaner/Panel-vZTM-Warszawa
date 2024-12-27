CREATE TABLE `contracts` (
  `id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `carrier_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `driver_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `current_state` varchar(40) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `initial_penalty_tasks` tinyint(3) UNSIGNED NOT NULL,
  `remaining_penalty_tasks` tinyint(3) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carrier_id` (`carrier_id`),
  ADD KEY `driver_id` (`driver_id`);