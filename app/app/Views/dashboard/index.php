<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header page-header--compact">
    <div>
        <p class="eyebrow"><?= e($restaurant['name'] ?? $user['restaurant_name']) ?></p>
        <h1><?= e(trans('dashboard.title')) ?></h1>
        <p><?= e(trans('dashboard.greeting', ['name' => $user['name']])) ?></p>
    </div>
    <a class="button button--primary" href="<?= e(url('/entries/create')) ?>">
        + <?= e(trans('entries.new')) ?>
    </a>
</section>

<section class="dashboard-overview">
    <article class="overview-hero">
        <div class="overview-hero__top">
            <span class="card__label"><?= e(trans('dashboard.current_balance')) ?></span>
            <span class="status-badge status-badge--dark"><?= e(trans('dashboard.mvp_status')) ?></span>
        </div>
        <strong class="overview-hero__amount"><?= e(format_currency($pendingTotals['pending_total'] ?? 0)) ?></strong>
        <p><?= e(trans('dashboard.balance_help', [
            'count' => $entryTotals['entry_count'] ?? 0,
            'total' => format_currency($entryTotals['net_total'] ?? 0),
        ])) ?></p>
        <div class="overview-hero__breakdown">
            <div>
                <span><?= e(trans('dashboard.cash_pending')) ?></span>
                <strong><?= e(format_currency($pendingTotals['cash_pending'] ?? 0)) ?></strong>
            </div>
            <div>
                <span><?= e(trans('dashboard.card_pending')) ?></span>
                <strong><?= e(format_currency($pendingTotals['card_net_pending'] ?? 0)) ?></strong>
            </div>
            <div>
                <span><?= e(trans('dashboard.paid_month')) ?></span>
                <strong><?= e(format_currency($pendingTotals['paid_total'] ?? 0)) ?></strong>
            </div>
        </div>
    </article>

    <div class="overview-stats">
        <a class="metric-card" href="<?= e(url('/employees')) ?>">
            <span class="metric-card__icon"><?= nav_icon('employees') ?></span>
            <span><?= e(trans('dashboard.active_employees')) ?></span>
            <strong><?= e((string) $activeEmployeeCount) ?></strong>
            <small><?= e(trans('dashboard.manage_employees')) ?> →</small>
        </a>
        <a class="metric-card" href="<?= e(url('/shifts')) ?>">
            <span class="metric-card__icon"><?= nav_icon('shifts') ?></span>
            <span><?= e(trans('dashboard.month_shifts')) ?></span>
            <strong><?= e((string) $currentMonthShiftCount) ?></strong>
            <small><?= e(trans('dashboard.manage_shifts')) ?> →</small>
        </a>
        <a class="metric-card metric-card--accent" href="<?= e(url('/settings')) ?>">
            <span class="metric-card__icon"><?= nav_icon('settings') ?></span>
            <span><?= e(trans('dashboard.card_fee')) ?></span>
            <strong><?= e(format_percentage($restaurant['default_card_fee'] ?? 25)) ?></strong>
            <small><?= e(trans('dashboard.card_fee_help')) ?> →</small>
        </a>
    </div>
</section>

<div class="dashboard-columns">
    <section class="section-card">
        <div class="section-card__header">
            <div>
                <span class="eyebrow"><?= e(trans('dashboard.recent_activity')) ?></span>
                <h2><?= e(trans('dashboard.recent_shifts')) ?></h2>
            </div>
            <a class="text-link" href="<?= e(url('/entries')) ?>"><?= e(trans('common.view')) ?> →</a>
        </div>

        <?php if ($recentEntries === []): ?>
            <div class="empty-state empty-state--compact">
                <strong><?= e(trans('dashboard.no_shifts')) ?></strong>
                <p><?= e(trans('dashboard.no_shifts_help')) ?></p>
            </div>
        <?php else: ?>
            <div class="compact-list">
                <?php foreach ($recentEntries as $entry): ?>
                    <a class="compact-list__item compact-list__item--polished" href="<?= e(url('/entries/' . $entry['id'])) ?>">
                        <span class="list-icon"><?= nav_icon($entry['shift_type'] === 'lunch' ? 'shifts' : 'entries') ?></span>
                        <div>
                            <strong><?= e(trans('shifts.type.' . $entry['shift_type'])) ?></strong>
                            <span><?= e(format_date($entry['shift_date'])) ?> · <?= e(trans('entries.people_count', ['count' => $entry['employee_count']])) ?></span>
                        </div>
                        <strong class="compact-list__amount"><?= e(format_currency($entry['total_net_amount'])) ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="section-card quick-actions-panel">
        <div class="section-card__header">
            <div>
                <span class="eyebrow"><?= e(trans('dashboard.quick_actions')) ?></span>
                <h2><?= e(trans('dashboard.quick_actions')) ?></h2>
            </div>
        </div>
        <p class="section-intro"><?= e(trans('dashboard.quick_actions_help')) ?></p>
        <div class="quick-actions-list">
            <a href="<?= e(url('/employees/create')) ?>">
                <span><?= nav_icon('employees') ?></span>
                <div><strong><?= e(trans('employees.new')) ?></strong><small><?= e(trans('dashboard.employee_action')) ?></small></div>
                <b>→</b>
            </a>
            <a href="<?= e(url('/shifts/create')) ?>">
                <span><?= nav_icon('shifts') ?></span>
                <div><strong><?= e(trans('shifts.new')) ?></strong><small><?= e(trans('dashboard.shift_action')) ?></small></div>
                <b>→</b>
            </a>
            <a href="<?= e(url('/settings')) ?>">
                <span><?= nav_icon('settings') ?></span>
                <div><strong><?= e(trans('nav.settings')) ?></strong><small><?= e(trans('dashboard.settings_action')) ?></small></div>
                <b>→</b>
            </a>
        </div>
    </section>
</div>
