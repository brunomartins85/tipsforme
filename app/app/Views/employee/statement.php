<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('employee.statement.eyebrow')) ?></p>
        <h1><?= e(trans('employee.statement.title')) ?></h1>
        <p><?= e(trans('employee.statement.subtitle')) ?></p>
    </div>
</section>

<section class="section-card">
    <?php if ($distributions === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('employee.no_entries')) ?></strong>
            <p><?= e(trans('employee.no_entries_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('shifts.date')) ?></th>
                    <th><?= e(trans('entries.shift')) ?></th>
                    <th><?= e(trans('entries.cash')) ?></th>
                    <th><?= e(trans('entries.card_net')) ?></th>
                    <th><?= e(trans('entries.total')) ?></th>
                    <th><?= e(trans('entries.status')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($distributions as $distribution): ?>
                    <?php
                    $cashPending = empty($distribution['cash_settlement_id']) && (float) $distribution['cash_amount'] > 0;
                    $cardPending = empty($distribution['card_settlement_id']) && (float) $distribution['card_net_amount'] > 0;
                    $statusKey = ($cashPending || $cardPending) ? 'pending' : 'paid';
                    ?>
                    <tr>
                        <td data-label="<?= e(trans('shifts.date')) ?>"><?= e(format_date($distribution['shift_date'])) ?></td>
                        <td data-label="<?= e(trans('entries.shift')) ?>">
                            <strong><?= e(trans('shifts.type.' . $distribution['shift_type'])) ?></strong>
                            <small class="table-subtext"><?= e(trans('entries.people_count', ['count' => $distribution['participant_count']])) ?></small>
                        </td>
                        <td data-label="<?= e(trans('entries.cash')) ?>"><?= e(format_currency($distribution['cash_amount'])) ?></td>
                        <td data-label="<?= e(trans('entries.card_net')) ?>">
                            <strong><?= e(format_currency($distribution['card_net_amount'])) ?></strong>
                            <small class="table-subtext"><?= e(trans('employee.card_fee_applied', ['percentage' => format_percentage($distribution['card_fee_percentage'])])) ?></small>
                        </td>
                        <td data-label="<?= e(trans('entries.total')) ?>"><strong><?= e(format_currency($distribution['total_amount'])) ?></strong></td>
                        <td data-label="<?= e(trans('entries.status')) ?>">
                            <span class="status-pill status-pill--<?= $statusKey === 'pending' ? 'open' : 'settled' ?>">
                                <?= e(trans('employee.status.' . $statusKey)) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
