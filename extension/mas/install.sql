--
-- Table structure for table `oc_mas_provider`
--
CREATE TABLE IF NOT EXISTS `oc_mas_provider` (
  `provider_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` varchar(32) NOT NULL COMMENT 'e.g., smtp, anthropic',
  `settings` text NOT NULL COMMENT 'JSON encoded settings like API keys, host, port etc.',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- You can add other tables for workflows, segments etc. here in the future.