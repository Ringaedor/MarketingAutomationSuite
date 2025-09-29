-- =====================================================================
-- 1. CONSENTI (GDPR, marketing, T&C, ecc.)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `mas_consent_definition` (
  `code`        VARCHAR(64)  NOT NULL PRIMARY KEY,          -- es. marketing_email
  `name`        VARCHAR(128) NOT NULL,
  `description` TEXT         NOT NULL,
  `version`     VARCHAR(16)  NOT NULL DEFAULT '1.0',
  `required`    TINYINT(1)   NOT NULL DEFAULT 0,            -- se obbligatorio per usare la piattaforma
  `created_at`  DATETIME     NOT NULL,
  `updated_at`  DATETIME     NOT NULL,
  KEY `idx_required` (`required`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mas_consent_log` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `code`        VARCHAR(64)     NOT NULL,
  `version`     VARCHAR(16)     NOT NULL,
  `action`      ENUM('accept','revoke') NOT NULL,
  `metadata`    JSON            NULL,
  `ip_address`  VARCHAR(45)     NULL,
  `user_agent`  VARCHAR(255)    NULL,
  `created_at`  DATETIME        NOT NULL,
  KEY `idx_customer_code` (`customer_id`,`code`),
  KEY `idx_code` (`code`),
  CONSTRAINT `fk_consent_definition`
    FOREIGN KEY (`code`) REFERENCES `mas_consent_definition` (`code`)
      ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- 2. EVENT QUEUE (per EventDispatcher asincrono)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `mas_event_queue` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `event_name`  VARCHAR(128)  NOT NULL,
  `payload`     JSON          NOT NULL,
  `status`      ENUM('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `priority`    TINYINT(3)    NOT NULL DEFAULT 0,
  `attempts`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `error`       TEXT          NULL,
  `scheduled_at` DATETIME     NOT NULL,
  `processed_at` DATETIME     NULL,
  `created_at`  DATETIME      NOT NULL,
  KEY `idx_status_scheduled` (`status`,`scheduled_at`),
  KEY `idx_event` (`event_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- 3. AUDIT LOG
-- =====================================================================

CREATE TABLE IF NOT EXISTS `mas_audit_log` (
  `event_id`      CHAR(32)      NOT NULL PRIMARY KEY,
  `category`      VARCHAR(32)   NOT NULL,                      -- es. security, data
  `action`        VARCHAR(64)   NOT NULL,
  `severity`      ENUM('critical','high','medium','low','info') NOT NULL DEFAULT 'info',
  `severity_level` TINYINT(1)   NOT NULL,                      -- denormalizzato per ordinare velocemente
  `user_id`       BIGINT UNSIGNED NULL,
  `customer_id`   BIGINT UNSIGNED NULL,
  `ip_address`    VARCHAR(45)   NULL,
  `user_agent`    VARCHAR(255)  NULL,
  `session_id`    VARCHAR(128)  NULL,
  `request_uri`   VARCHAR(255)  NULL,
  `request_method` CHAR(6)      NULL,
  `context`       JSON          NOT NULL,
  `timestamp`     DATETIME      NOT NULL,
  `created_at`    DATETIME      NOT NULL,
  KEY `idx_cat_sev_time` (`category`,`severity_level`,`created_at`),
  KEY `idx_user_time` (`user_id`,`created_at`),
  KEY `idx_customer_time` (`customer_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mas_audit_log_archive` LIKE `mas_audit_log`;


-- =====================================================================
-- 4. OPTIONAL: PROVIDER METRICS (per Gateway monitoring)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `mas_provider_metrics` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `provider`     VARCHAR(64)   NOT NULL,
  `category`     VARCHAR(32)   NOT NULL,       -- ai, message, payment
  `action`       VARCHAR(32)   NOT NULL,       -- chat, authorize, send, ecc.
  `success`      TINYINT(1)    NOT NULL,
  `response_ms`  INT UNSIGNED  NOT NULL,
  `created_at`   DATETIME      NOT NULL,
  KEY `idx_provider_action` (`provider`,`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
