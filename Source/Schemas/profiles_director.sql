CREATE TABLE `profiles_director` (
  `profile_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `protected` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `profiles_director`
  ADD KEY `profile_id` (`profile_id`);