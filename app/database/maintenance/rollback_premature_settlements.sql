-- TipsForMe v1.0.1
-- Desfaz somente fechamentos criados antes da data permitida.
-- Use primeiro preview_premature_settlements.sql para conferir os registros.

SET NAMES utf8mb4;
START TRANSACTION;

CREATE TEMPORARY TABLE premature_settlement_ids (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY
);

INSERT INTO premature_settlement_ids (id)
SELECT settlements.id
FROM settlements
INNER JOIN restaurants ON restaurants.id = settlements.restaurant_id
WHERE
    (
        settlements.settlement_type = 'first_half'
        AND DATE(settlements.created_at) < ADDDATE(
            settlements.reference_month,
            restaurants.first_half_closing_day - 1
        )
    )
    OR
    (
        settlements.settlement_type = 'month_end'
        AND DATE(settlements.created_at) < LAST_DAY(settlements.reference_month)
    );

UPDATE tip_distributions
INNER JOIN premature_settlement_ids
    ON premature_settlement_ids.id = tip_distributions.cash_settlement_id
SET tip_distributions.cash_settlement_id = NULL;

UPDATE tip_distributions
INNER JOIN premature_settlement_ids
    ON premature_settlement_ids.id = tip_distributions.card_settlement_id
SET tip_distributions.card_settlement_id = NULL;

DELETE settlement_payments
FROM settlement_payments
INNER JOIN premature_settlement_ids
    ON premature_settlement_ids.id = settlement_payments.settlement_id;

DELETE settlements
FROM settlements
INNER JOIN premature_settlement_ids
    ON premature_settlement_ids.id = settlements.id;

UPDATE tip_entries
INNER JOIN (
    SELECT
        tip_distributions.restaurant_id,
        tip_distributions.tip_entry_id,
        SUM(
            CASE
                WHEN
                    (tip_distributions.cash_amount > 0 AND tip_distributions.cash_settlement_id IS NULL)
                    OR
                    (tip_distributions.card_net_amount > 0 AND tip_distributions.card_settlement_id IS NULL)
                THEN 1 ELSE 0
            END
        ) AS pending_count,
        SUM(
            CASE
                WHEN tip_distributions.cash_settlement_id IS NOT NULL
                    OR tip_distributions.card_settlement_id IS NOT NULL
                THEN 1 ELSE 0
            END
        ) AS paid_count
    FROM tip_distributions
    GROUP BY tip_distributions.restaurant_id, tip_distributions.tip_entry_id
) distribution_status
    ON distribution_status.restaurant_id = tip_entries.restaurant_id
   AND distribution_status.tip_entry_id = tip_entries.id
SET tip_entries.status = CASE
    WHEN distribution_status.pending_count = 0 THEN 'settled'
    WHEN distribution_status.paid_count > 0 THEN 'partially_settled'
    ELSE 'open'
END;

DROP TEMPORARY TABLE premature_settlement_ids;
COMMIT;
