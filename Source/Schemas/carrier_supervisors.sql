CREATE TABLE `carrier_supervisors` (
  `carrier_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `supervisor_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `carrier_supervisors`
  ADD UNIQUE KEY `carrier_supervisor_key` (`carrier_id`,`supervisor_id`),
  ADD KEY `carrier_id_key` (`carrier_id`);