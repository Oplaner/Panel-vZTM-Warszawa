CREATE TABLE `carrier_supervisors` (
  `carrier_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL,
  `supervisor_id` char(36) CHARACTER SET ascii COLLATE ascii_general_nopad_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

ALTER TABLE `carrier_supervisors`
  ADD KEY `carrier_id` (`carrier_id`);