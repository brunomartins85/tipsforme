-- TipsForMe - Módulo 02
-- Colaboradores, turnos e presença por restaurante.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS employees (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NULL,
    position VARCHAR(80) NOT NULL,
    language ENUM('pt', 'en') NOT NULL DEFAULT 'pt',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY employees_restaurant_id_id_unique (restaurant_id, id),
    UNIQUE KEY employees_restaurant_email_unique (restaurant_id, email),
    UNIQUE KEY employees_user_id_unique (user_id),
    KEY employees_restaurant_status_index (restaurant_id, status),
    CONSTRAINT employees_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT employees_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shifts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    shift_date DATE NOT NULL,
    shift_type ENUM('lunch', 'dinner') NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    notes VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY shifts_restaurant_id_id_unique (restaurant_id, id),
    UNIQUE KEY shifts_restaurant_date_type_unique (restaurant_id, shift_date, shift_type),
    KEY shifts_restaurant_date_index (restaurant_id, shift_date),
    KEY shifts_created_by_index (created_by),
    KEY shifts_updated_by_index (updated_by),
    CONSTRAINT shifts_restaurant_id_foreign
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT shifts_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT shifts_updated_by_foreign
        FOREIGN KEY (updated_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shift_employees (
    shift_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (shift_id, employee_id),
    KEY shift_employees_restaurant_index (restaurant_id),
    KEY shift_employees_employee_index (employee_id, restaurant_id),
    CONSTRAINT shift_employees_shift_tenant_foreign
        FOREIGN KEY (restaurant_id, shift_id) REFERENCES shifts(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT shift_employees_employee_tenant_foreign
        FOREIGN KEY (restaurant_id, employee_id) REFERENCES employees(restaurant_id, id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
