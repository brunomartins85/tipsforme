<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('settlements.receipt_eyebrow')) ?></p>
        <h1><?= e(trans('settlements.type.' . $settlement['settlement_type'])) ?></h1>
        <p><?= e(format_month($settlement['reference_month'])) ?> · <?= e(trans('settlements.paid_on', ['date' => format_date($settlement['payment_date'])])) ?></p>
    </div>
    <div class="page-header__actions">
        <a class="button button--outline" href="<?= e(url('/settlements')) ?>"><?= e(trans('common.back')) ?></a>
        <span class="status-pill status-pill--settled"><?= e(trans('settlements.status.paid')) ?></span>
    </div>
</section>

<section class="summary-grid">
    <article class="summary-card">
        <span><?= e(trans('entries.cash')) ?></span>
        <strong><?= e(format_currency($settlement['cash_total'])) ?></strong>
    </article>
    <article class="summary-card">
        <span><?= e(trans('entries.card_gross')) ?></span>
        <strong><?= e(format_currency($settlement['card_gross_total'])) ?></strong>
    </article>
    <article class="summary-card summary-card--fee">
        <span><?= e(trans('entries.fee')) ?></span>
        <strong>- <?= e(format_currency($settlement['card_fee_total'])) ?></strong>
    </article>
    <article class="summary-card summary-card--primary">
        <span><?= e(trans('settlements.total_paid')) ?></span>
        <strong><?= e(format_currency($settlement['total_paid'])) ?></strong>
    </article>
</section>

<section class="settlement-period-card">
    <div>
        <span><?= e(trans('settlements.cash_period')) ?></span>
        <strong><?= e(format_date($settlement['cash_period_start'])) ?> – <?= e(format_date($settlement['cash_period_end'])) ?></strong>
    </div>
    <div>
        <span><?= e(trans('settlements.card_period')) ?></span>
        <strong>
            <?php if (!empty($settlement['card_period_start'])): ?>
                <?= e(format_date($settlement['card_period_start'])) ?> – <?= e(format_date($settlement['card_period_end'])) ?>
            <?php else: ?>
                <?= e(trans('settlements.not_included')) ?>
            <?php endif; ?>
        </strong>
    </div>
    <div>
        <span><?= e(trans('settlements.registered_by')) ?></span>
        <strong><?= e($settlement['created_by_name']) ?></strong>
    </div>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('settlements.payment_history')) ?></p>
            <h2><?= e(trans('settlements.employee_values')) ?></h2>
        </div>
        <span class="status-badge"><?= e(trans('settlements.employee_count', ['count' => count($payments)])) ?></span>
    </div>

    <div class="responsive-table">
        <table>
            <thead>
            <tr>
                <th><?= e(trans('employees.employee')) ?></th>
                <th><?= e(trans('entries.cash')) ?></th>
                <th><?= e(trans('entries.card_gross')) ?></th>
                <th><?= e(trans('entries.fee')) ?></th>
                <th><?= e(trans('entries.card_net')) ?></th>
                <th><?= e(trans('settlements.total_paid')) ?></th>
                <th><?= e(trans('settlements.status_label')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td data-label="<?= e(trans('employees.employee')) ?>">
                        <div class="person-cell">
                            <span class="avatar avatar--small"><?= e(text_initial($payment['name'])) ?></span>
                            <span>
                                <strong><?= e($payment['name']) ?></strong>
                                <small><?= e($payment['position']) ?></small>
                            </span>
                        </div>
                    </td>
                    <td data-label="<?= e(trans('entries.cash')) ?>"><?= e(format_currency($payment['cash_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.card_gross')) ?>"><?= e(format_currency($payment['card_gross_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.fee')) ?>">- <?= e(format_currency($payment['card_fee_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.card_net')) ?>"><?= e(format_currency($payment['card_net_amount'])) ?></td>
                    <td data-label="<?= e(trans('settlements.total_paid')) ?>"><strong><?= e(format_currency($payment['total_amount'])) ?></strong></td>
                    <td data-label="<?= e(trans('settlements.status_label')) ?>">
                        <span class="status-pill status-pill--settled"><?= e(trans('settlements.status.paid')) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if (!empty($settlement['notes'])): ?>
    <section class="section-card">
        <p class="eyebrow"><?= e(trans('settlements.notes')) ?></p>
        <p><?= nl2br(e($settlement['notes'])) ?></p>
    </section>
<?php endif; ?>
