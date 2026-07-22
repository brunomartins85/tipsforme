-- TipsForMe - Módulo 06
-- Configurações operacionais do restaurante.

SET NAMES utf8mb4;

ALTER TABLE restaurants
    ADD COLUMN default_language ENUM('pt', 'en') NOT NULL DEFAULT 'pt' AFTER timezone,
    ADD COLUMN first_half_closing_day TINYINT UNSIGNED NOT NULL DEFAULT 15 AFTER default_card_fee,
    ADD COLUMN password_reset_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER first_half_closing_day;
