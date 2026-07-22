<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('settlements.eyebrow')) ?></p>
        <h1><?= e(trans('settlements.title')) ?></h1>
        <p><?= e(trans('settlements.subtitle')) ?></p>
    </div>

    <form class="month-filter" method="GET" action="<?= e(url('/settlements')) ?>">
        <label class="field">
            <span><?= e(trans('settlements.reference_month')) ?></span>
            <input type="month" name="month" value="<?= e($month) ?>">
        </label>
        <button class="button button--outline" type="submit"><?= e(trans('common.filter')) ?></button>
    </form>
</section>

<section class="settlement-options">
    <?php foreach ([$firstHalf, $monthEnd] as $summary): ?>
        <?php
        $hasPending = $summary['totals']['total_cents'] > 0;
        $isAvailable = (bool) ($summary['availability']['available'] ?? false);
        ?>
        <article class="settlement-option <?= $summary['type'] === 'month_end' ? 'settlement-option--primary' : '' ?>">
            <div>
                <span class="eyebrow"><?= e(format_month($summary['reference_month'])) ?></span>
                <h2><?= e(trans('settlements.type.' . $summary['type'])) ?></h2>
                <p><?= e(trans('settlements.period.' . $summary['type'], [
                    'cash_start' => format_date($summary['periods']['cash_start']),
                    'cash_end' => format_date($summary['periods']['cash_end']),
                    'card_start' => $summary['periods']['card_start'] ? format_date($summary['periods']['card_start']) : '',
                    'card_end' => $summary['periods']['card_end'] ? format_date($summary['periods']['card_end']) : '',
                ])) ?></p>
            </div>

            <div class="settlement-option__totals">
                <div>
                    <span><?= e(trans('entries.cash')) ?></span>
                    <strong><?= e(format_cents($summary['totals']['cash_cents'])) ?></strong>
                </div>
                <?php if ($summary['type'] === 'month_end'): ?>
                    <div>
                        <span><?= e(trans('entries.card_net')) ?></span>
                        <strong><?= e(format_cents($summary['totals']['card_net_cents'])) ?></strong>
                    </div>
                <?php endif; ?>
                <div class="settlement-option__total">
                    <span><?= e(trans('settlements.pending_total')) ?></span>
                    <strong><?= e(format_cents($summary['totals']['total_cents'])) ?></strong>
                </div>
            </div>

            <div class="settlement-option__footer">
                <span><?= e(trans('settlements.employee_count', ['count' => count($summary['payments'])])) ?></span>
                <?php if ($hasPending && $isAvailable): ?>
                    <a class="button <?= $summary['type'] === 'month_end' ? 'button--light' : 'button--primary' ?>" href="<?= e(url('/settlements/preview?type=' . $summary['type'] . '&month=' . $month)) ?>">
                        <?= e(trans('settlements.review')) ?>
                    </a>
                <?php elseif ($hasPending): ?>
                    <span class="status-pill status-pill--open">
                        <?= e(trans('settlements.available_on', [
                            'date' => format_date($summary['availability']['available_on']),
                        ])) ?>
                    </span>
                <?php else: ?>
                    <span class="status-pill status-pill--settled"><?= e(trans('settlements.nothing_pending')) ?></span>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('settlements.history_eyebrow')) ?></p>
            <h2><?= e(trans('settlements.history')) ?></h2>
        </div>
    </div>

    <?php if ($history === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('settlements.history_empty')) ?></strong>
            <p><?= e(trans('settlements.history_empty_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('settlements.payment_date')) ?></th>
                    <th><?= e(trans('settlements.closing')) ?></th>
                    <th><?= e(trans('entries.cash')) ?></th>
                    <th><?= e(trans('entries.card_net')) ?></th>
                    <th><?= e(trans('settlements.employees_paid')) ?></th>
                    <th><?= e(trans('settlements.total_paid')) ?></th>
                    <th class="table-actions-heading"><?= e(trans('common.actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $settlement): ?>
                    <tr>
                        <td data-label="<?= e(trans('settlements.payment_date')) ?>">
                            <strong><?= e(format_date($settlement['payment_date'])) ?></strong>
                        </td>
                        <td data-label="<?= e(trans('settlements.closing')) ?>">
                            <?= e(trans('settlements.type.' . $settlement['settlement_type'])) ?>
                            <small class="table-subtext"><?= e(format_month($settlement['reference_month'])) ?></small>
                        </td>
                        <td data-label="<?= e(trans('entries.cash')) ?>"><?= e(format_currency($settlement['cash_total'])) ?></td>
                        <td data-label="<?= e(trans('entries.card_net')) ?>"><?= e(format_currency($settlement['card_net_total'])) ?></td>
                        <td data-label="<?= e(trans('settlements.employees_paid')) ?>"><?= e((string) $settlement['employee_count']) ?></td>
                        <td data-label="<?= e(trans('settlements.total_paid')) ?>"><strong><?= e(format_currency($settlement['total_paid'])) ?></strong></td>
                        <td data-label="<?= e(trans('common.actions')) ?>">
                            <div class="table-actions">
                                <a class="button button--outline button--small" href="<?= e(url('/settlements/' . $settlement['id'])) ?>">
                                    <?= e(trans('common.view')) ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
