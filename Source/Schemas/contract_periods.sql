CREATE TABLE `contract_periods` (
  `id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `contract_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `state` varchar(40) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `valid_from` datetime(6) NOT NULL,
  `authorized_by_user_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `valid_to` datetime(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `contract_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id_key` (`contract_id`);