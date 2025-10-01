--
-- Drop existing tables to ensure a clean installation
--
DROP TABLE IF EXISTS `oc_mas_workflow`;
DROP TABLE IF EXISTS `oc_mas_segment_rule`;
DROP TABLE IF EXISTS `oc_mas_segment`;
DROP TABLE IF EXISTS `oc_mas_provider`;
DROP TABLE IF EXISTS `oc_mas_template`;
DROP TABLE IF EXISTS `oc_mas_consent_log`;
DROP TABLE IF EXISTS `oc_mas_consent_definition`;
DROP TABLE IF EXISTS `oc_mas_analytics`;

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

--
-- Table structure for table `oc_mas_template`
--
CREATE TABLE IF NOT EXISTS `oc_mas_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` text NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `oc_mas_consent_definition`
--
CREATE TABLE IF NOT EXISTS `oc_mas_consent_definition` (
  `consent_definition_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`consent_definition_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `oc_mas_consent_log`
--
CREATE TABLE IF NOT EXISTS `oc_mas_consent_log` (
  `consent_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `consent_definition_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1 = Granted, 0 = Revoked',
  `source` varchar(255) NOT NULL COMMENT 'e.g., checkout, registration_form',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`consent_log_id`),
  KEY `customer_id` (`customer_id`),
  KEY `consent_definition_id` (`consent_definition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `oc_mas_analytics`
--
CREATE TABLE IF NOT EXISTS `oc_mas_analytics` (
  `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `node_id` varchar(64) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `event_type` varchar(64) NOT NULL COMMENT 'e.g., email_sent, workflow_started',
  `event_data` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`analytics_id`),
  KEY `workflow_id` (`workflow_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;