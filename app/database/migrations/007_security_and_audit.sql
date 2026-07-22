-- TipsForMe - Módulo 07
-- Segurança de acesso, limite de tentativas e auditoria administrativa.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email_hash CHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY login_attempts_lookup_index (email_hash, ip_address, success, attempted_at),
    KEY login_attempts_cleanup_index (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NULL,
    metadata TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY audit_logs_restaurant_date_index (restaurant_id, created_at),
    KEY audit_logs_restaurant_action_index (restaurant_id, action, created_at),
    KEY audit_logs_user_index (user_id, created_at),
    CONSTRAINT audit_logs_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT audit_logs_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
