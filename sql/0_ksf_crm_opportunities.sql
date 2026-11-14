-- ksf_FA_Opportunity: opportunity pipeline table

CREATE TABLE IF NOT EXISTS `0_ksf_crm_opportunities` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT,
  `opportunity_name` VARCHAR(100) NOT NULL DEFAULT '',
  `stage`         VARCHAR(30)  NOT NULL DEFAULT 'prospecting',
  `amount`        DECIMAL(15,2) NOT NULL DEFAULT 0,
  `probability`   INT(3)       NOT NULL DEFAULT 0,
  `expected_close` DATE        NULL,
  `customer_id`   INT(11)      NOT NULL DEFAULT 0,
  `contact_id`    INT(11)      NOT NULL DEFAULT 0,
  `description`   TEXT,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stage` (`stage`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
