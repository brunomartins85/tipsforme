-- TipsForMe - Módulo 05
-- Painel individual do colaborador, ativação de acesso e recuperação de senha.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    purpose ENUM('activation', 'password_reset') NOT NULL DEFAULT 'password_reset',
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY password_reset_tokens_hash_unique (token_hash),
    KEY password_reset_tokens_user_index (user_id, expires_at),
    KEY password_reset_tokens_restaurant_index (restaurant_id, expires_at),
    CONSTRAINT password_reset_tokens_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT password_reset_tokens_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
