CREATE TABLE `profiles_driver` (
  `profile_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `initial_penalty_multiplier` tinyint(3) UNSIGNED NOT NULL,
  `acquired_penalty_multiplier` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;


ALTER TABLE `profiles_driver`
  ADD KEY `profile_id` (`profile_id`);