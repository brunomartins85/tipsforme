<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e($employee['position'] ?? trans('employee.portal')) ?></p>
        <h1><?= e(trans('employee.dashboard.title')) ?></h1>
        <p><?= e(trans('employee.dashboard.greeting', ['name' => $employee['name'] ?? ''])) ?></p>
    </div>
    <span class="status-badge"><?= e(trans('employee.next_payment', ['date' => format_date($nextPaymentDate)])) ?></span>
</section>

<section class="employee-balance-grid">
    <article class="employee-balance-card employee-balance-card--hero">
        <span><?= e(trans('employee.balance.pending')) ?></span>
        <strong><?= e(format_currency($balance['total_pending'] ?? 0)) ?></strong>
        <small><?= e(trans('employee.balance.pending_help')) ?></small>
    </article>

    <article class="employee-balance-card">
        <span><?= e(trans('entries.cash')) ?></span>
        <strong><?= e(format_currency($balance['cash_pending'] ?? 0)) ?></strong>
        <small><?= e(trans('employee.balance.cash_help')) ?></small>
    </article>

    <article class="employee-balance-card">
        <span><?= e(trans('entries.card_net')) ?></span>
        <strong><?= e(format_currency($balance['card_net_pending'] ?? 0)) ?></strong>
        <small><?= e(trans('employee.balance.card_help', ['fee' => format_currency($balance['card_fee_pending'] ?? 0)])) ?></small>
    </article>

    <article class="employee-balance-card employee-balance-card--dark">
        <span><?= e(trans('employee.month_total')) ?></span>
        <strong><?= e(format_currency($monthTotals['total_amount'] ?? 0)) ?></strong>
        <small><?= e(trans('employee.month_shifts', ['count' => $monthTotals['shift_count'] ?? 0])) ?></small>
    </article>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <span class="eyebrow"><?= e(trans('employee.recent_eyebrow')) ?></span>
            <h2><?= e(trans('employee.recent_title')) ?></h2>
        </div>
        <a class="button button--outline button--small" href="<?= e(url('/my/statement')) ?>">
            <?= e(trans('employee.view_all')) ?>
        </a>
    </div>

    <?php if ($recentDistributions === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('employee.no_entries')) ?></strong>
            <p><?= e(trans('employee.no_entries_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="employee-entry-list">
            <?php foreach ($recentDistributions as $distribution): ?>
                <?php
                $cashPending = empty($distribution['cash_settlement_id']) && (float) $distribution['cash_amount'] > 0;
                $cardPending = empty($distribution['card_settlement_id']) && (float) $distribution['card_net_amount'] > 0;
                $isPending = $cashPending || $cardPending;
                ?>
                <article class="employee-entry-item">
                    <div class="employee-entry-item__date">
                        <strong><?= e(date('d', strtotime($distribution['shift_date']))) ?></strong>
                        <span><?= e(trans('months.' . date('m', strtotime($distribution['shift_date'])))) ?></span>
                    </div>
                    <div class="employee-entry-item__main">
                        <strong><?= e(trans('shifts.type.' . $distribution['shift_type'])) ?></strong>
                        <span><?= e(trans('entries.people_count', ['count' => $distribution['participant_count']])) ?></span>
                    </div>
                    <div class="employee-entry-item__amount">
                        <strong><?= e(format_currency($distribution['total_amount'])) ?></strong>
                        <span class="status-pill <?= $isPending ? 'status-pill--open' : 'status-pill--settled' ?>">
                            <?= e($isPending ? trans('employee.status.pending') : trans('employee.status.paid')) ?>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
