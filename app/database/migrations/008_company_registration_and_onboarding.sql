-- TipsForMe - Módulo 10
-- Cadastro público de empresas, confirmação de e-mail e onboarding europeu.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

ALTER TABLE restaurants
    MODIFY COLUMN status ENUM('pending_verification', 'active', 'suspended', 'inactive') NOT NULL DEFAULT 'active',
    ADD COLUMN legal_name VARCHAR(160) NULL AFTER name,
    ADD COLUMN trade_name VARCHAR(160) NULL AFTER legal_name,
    ADD COLUMN country_code CHAR(2) NULL AFTER trade_name,
    ADD COLUMN company_registration_number VARCHAR(80) NULL AFTER country_code,
    ADD COLUMN vat_number VARCHAR(40) NULL AFTER company_registration_number,
    ADD COLUMN business_type ENUM('restaurant', 'cafe', 'bar', 'hotel', 'other') NULL AFTER vat_number,
    ADD COLUMN address_line1 VARCHAR(190) NULL AFTER business_type,
    ADD COLUMN address_line2 VARCHAR(190) NULL AFTER address_line1,
    ADD COLUMN postal_code VARCHAR(32) NULL AFTER address_line2,
    ADD COLUMN city VARCHAR(120) NULL AFTER postal_code,
    ADD COLUMN terms_accepted_at DATETIME NULL AFTER password_reset_enabled,
    ADD COLUMN terms_version VARCHAR(32) NULL AFTER terms_accepted_at,
    ADD COLUMN privacy_acknowledged_at DATETIME NULL AFTER terms_version,
    ADD COLUMN privacy_version VARCHAR(32) NULL AFTER privacy_acknowledged_at,
    ADD COLUMN marketing_consent_at DATETIME NULL AFTER privacy_version,
    ADD COLUMN onboarding_completed_at DATETIME NULL AFTER marketing_consent_at,
    ADD COLUMN registration_ip VARCHAR(45) NULL AFTER onboarding_completed_at;

ALTER TABLE users
    ADD COLUMN email_verified_at DATETIME NULL AFTER status;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email_verification_tokens_hash_unique (token_hash),
    KEY email_verification_tokens_user_index (user_id, used_at, expires_at),
    CONSTRAINT email_verification_tokens_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT email_verification_tokens_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registration_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY registration_attempts_lookup_index (ip_address, attempted_at),
    KEY registration_attempts_cleanup_index (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS data_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('access', 'export', 'correction', 'deletion', 'restriction', 'objection') NOT NULL,
    details TEXT NULL,
    status ENUM('received', 'in_review', 'completed', 'rejected') NOT NULL DEFAULT 'received',
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY data_requests_restaurant_status_index (restaurant_id, status, requested_at),
    KEY data_requests_user_index (user_id, requested_at),
    CONSTRAINT data_requests_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT data_requests_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contas existentes já estavam em produção antes desta migração.
UPDATE restaurants
SET legal_name = COALESCE(NULLIF(legal_name, ''), name),
    trade_name = COALESCE(NULLIF(trade_name, ''), name),
    onboarding_completed_at = COALESCE(onboarding_completed_at, created_at)
WHERE onboarding_completed_at IS NULL
   OR legal_name IS NULL
   OR trade_name IS NULL;

UPDATE users
SET email_verified_at = COALESCE(email_verified_at, created_at)
WHERE email_verified_at IS NULL;
