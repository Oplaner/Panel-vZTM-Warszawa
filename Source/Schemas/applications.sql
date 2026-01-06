CREATE TABLE `applications` (
  `id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `login` int(10) UNSIGNED NOT NULL,
  `username` varchar(120) NOT NULL,
  `date_of_birth` date NOT NULL,
  `passed_exam_proof_url` varchar(100) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `motivation` varchar(500) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `status` varchar(40) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `validation_code` char(6) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL,
  `assigned_carrier_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL,
  `resolution_note` varchar(200) DEFAULT NULL,
  `resolved_at` datetime(6) DEFAULT NULL,
  `resolved_by_user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_key` (`login`);