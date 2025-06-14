CREATE TABLE `personnel_profile_privileges` (
  `personnel_profile_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `privilege_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `personnel_profile_privileges`
  ADD KEY `privilege_set_id` (`personnel_profile_id`);