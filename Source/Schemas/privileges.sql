CREATE TABLE `privileges` (
  `id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `scope` varchar(80) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `associated_entity_type` varchar(40) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL,
  `associated_entity_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `privileges`
  ADD PRIMARY KEY (`id`);