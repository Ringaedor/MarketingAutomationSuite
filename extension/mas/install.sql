-- Drop existing tables to ensure a clean installation
DROP TABLE IF EXISTS `oc_mas_template`;
DROP TABLE IF EXISTS `oc_mas_provider`;

-- Table structure for extension_mas_template
CREATE TABLE `oc_mas_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `html_content` text NOT NULL,
  `text_content` text NOT NULL,
  `type` enum('email','sms') NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for extension_mas_provider
CREATE TABLE `oc_mas_provider` (
  `provider_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` varchar(32) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Drop existing tables
DROP TABLE IF EXISTS `oc_mas_segment`;
DROP TABLE IF EXISTS `oc_mas_segment_rule`;
DROP TABLE IF EXISTS `oc_mas_workflow`;
DROP TABLE IF EXISTS `oc_mas_analytics`;

-- Table structure for mas_segment
CREATE TABLE `oc_mas_segment` (
  `segment_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for mas_segment_rule
CREATE TABLE `oc_mas_segment_rule` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `segment_id` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `operator` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `segment_id` (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for mas_workflow
CREATE TABLE `oc_mas_workflow` (
  `workflow_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `workflow_data` longtext NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for mas_analytics
CREATE TABLE `oc_mas_analytics` (
  `analytics_id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `event_type` varchar(64) NOT NULL,
  `event_data` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`analytics_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;