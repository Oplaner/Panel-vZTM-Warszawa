CREATE TABLE `profiles_personnel` (
  `profile_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `profiles_personnel`
  ADD UNIQUE KEY `profile_id_key` (`profile_id`);