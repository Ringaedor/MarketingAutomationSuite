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

--
-- Table structure for table `oc_mas_segment`
--
CREATE TABLE IF NOT EXISTS `oc_mas_segment` (
  `segment_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `oc_mas_segment_rule`
--
CREATE TABLE IF NOT EXISTS `oc_mas_segment_rule` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `segment_id` int(11) NOT NULL,
  `type` varchar(64) NOT NULL COMMENT 'e.g., customer_total_orders, customer_group',
  `operator` varchar(32) NOT NULL COMMENT 'e.g., >, <, =, IN',
  `value` text NOT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `segment_id` (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `oc_mas_workflow`
--
CREATE TABLE IF NOT EXISTS `oc_mas_workflow` (
  `workflow_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Inactive, 1 = Active',
  `workflow_data` longtext NOT NULL COMMENT 'JSON containing the workflow nodes and connections',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;