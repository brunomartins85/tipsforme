-- TipsForMe v1.0.1
-- Lista fechamentos criados antes da data permitida.

SELECT
    settlements.id,
    restaurants.name AS restaurant_name,
    settlements.settlement_type,
    settlements.reference_month,
    settlements.payment_date,
    settlements.total_paid,
    settlements.created_at,
    CASE
        WHEN settlements.settlement_type = 'first_half'
            THEN ADDDATE(settlements.reference_month, restaurants.first_half_closing_day - 1)
        ELSE LAST_DAY(settlements.reference_month)
    END AS allowed_from
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
    )
ORDER BY settlements.created_at DESC;
