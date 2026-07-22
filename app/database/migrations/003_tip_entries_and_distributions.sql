-- TipsForMe - Módulo 03
-- Lançamentos de gorjetas em dinheiro e multibanco, com divisão automática.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS tip_entries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    shift_id BIGINT UNSIGNED NOT NULL,
    cash_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_gross_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_fee_percentage DECIMAL(5,2) NOT NULL DEFAULT 25.00,
    card_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('open', 'settled') NOT NULL DEFAULT 'open',
    notes VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY tip_entries_restaurant_id_id_unique (restaurant_id, id),
    UNIQUE KEY tip_entries_restaurant_shift_unique (restaurant_id, shift_id),
    KEY tip_entries_restaurant_status_index (restaurant_id, status),
    KEY tip_entries_created_by_index (created_by),
    KEY tip_entries_updated_by_index (updated_by),
    CONSTRAINT tip_entries_shift_tenant_foreign
        FOREIGN KEY (restaurant_id, shift_id) REFERENCES shifts(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT tip_entries_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT tip_entries_updated_by_foreign
        FOREIGN KEY (updated_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tip_distributions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    tip_entry_id BIGINT UNSIGNED NOT NULL,
    shift_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    cash_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_gross_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY tip_distributions_entry_employee_unique (tip_entry_id, employee_id),
    KEY tip_distributions_restaurant_employee_index (restaurant_id, employee_id),
    KEY tip_distributions_restaurant_shift_index (restaurant_id, shift_id),
    CONSTRAINT tip_distributions_entry_tenant_foreign
        FOREIGN KEY (restaurant_id, tip_entry_id) REFERENCES tip_entries(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT tip_distributions_shift_tenant_foreign
        FOREIGN KEY (restaurant_id, shift_id) REFERENCES shifts(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT tip_distributions_employee_tenant_foreign
        FOREIGN KEY (restaurant_id, employee_id) REFERENCES employees(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
