-- TipsForMe - Módulo 04
-- Fechamentos quinzenais, pagamentos e histórico por colaborador.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

ALTER TABLE tip_entries
    MODIFY COLUMN status ENUM('open', 'partially_settled', 'settled') NOT NULL DEFAULT 'open';

CREATE TABLE IF NOT EXISTS settlements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    settlement_type ENUM('first_half', 'month_end') NOT NULL,
    reference_month DATE NOT NULL,
    cash_period_start DATE NOT NULL,
    cash_period_end DATE NOT NULL,
    card_period_start DATE NULL,
    card_period_end DATE NULL,
    payment_date DATE NOT NULL,
    cash_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_gross_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_fee_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_net_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    employee_count INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('paid') NOT NULL DEFAULT 'paid',
    notes VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY settlements_restaurant_id_id_unique (restaurant_id, id),
    KEY settlements_restaurant_month_index (restaurant_id, reference_month),
    KEY settlements_restaurant_payment_date_index (restaurant_id, payment_date),
    KEY settlements_created_by_index (created_by),
    CONSTRAINT settlements_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT settlements_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settlement_payments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    settlement_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    cash_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_gross_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    card_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    status ENUM('paid') NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY settlement_payments_settlement_employee_unique (settlement_id, employee_id),
    KEY settlement_payments_restaurant_employee_index (restaurant_id, employee_id),
    KEY settlement_payments_restaurant_date_index (restaurant_id, payment_date),
    CONSTRAINT settlement_payments_settlement_tenant_foreign
        FOREIGN KEY (restaurant_id, settlement_id) REFERENCES settlements(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT settlement_payments_employee_tenant_foreign
        FOREIGN KEY (restaurant_id, employee_id) REFERENCES employees(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE tip_distributions
    ADD COLUMN cash_settlement_id BIGINT UNSIGNED NULL AFTER total_amount,
    ADD COLUMN card_settlement_id BIGINT UNSIGNED NULL AFTER cash_settlement_id,
    ADD KEY tip_distributions_cash_settlement_index (restaurant_id, cash_settlement_id),
    ADD KEY tip_distributions_card_settlement_index (restaurant_id, card_settlement_id),
    ADD CONSTRAINT tip_distributions_cash_settlement_tenant_foreign
        FOREIGN KEY (restaurant_id, cash_settlement_id) REFERENCES settlements(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    ADD CONSTRAINT tip_distributions_card_settlement_tenant_foreign
        FOREIGN KEY (restaurant_id, card_settlement_id) REFERENCES settlements(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;
