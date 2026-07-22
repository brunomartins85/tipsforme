<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header page-header--compact">
    <div>
        <p class="eyebrow"><?= e(trans('reports.eyebrow')) ?></p>
        <h1><?= e(trans('reports.title')) ?></h1>
        <p><?= e(trans('reports.subtitle')) ?></p>
    </div>

    <div class="page-header__actions report-export-actions">
        <?php
        $filterQuery = '?month=' . urlencode($month)
            . ($employeeId !== null ? '&employee_id=' . $employeeId : '');
        ?>
        <a class="button button--outline" href="<?= e(url('/reports/export/csv') . $filterQuery) ?>">
            <?= e(trans('reports.export_csv')) ?>
        </a>
        <a class="button button--primary" href="<?= e(url('/reports/export/pdf') . $filterQuery) ?>">
            <?= e(trans('reports.export_pdf')) ?>
        </a>
    </div>
</section>

<section class="section-card report-filter-card">
    <form class="report-filter" method="GET" action="<?= e(url('/reports')) ?>">
        <label class="field">
            <span><?= e(trans('reports.reference_month')) ?></span>
            <input type="month" name="month" value="<?= e($month) ?>" required>
        </label>

        <label class="field">
            <span><?= e(trans('reports.employee_filter')) ?></span>
            <select name="employee_id">
                <option value=""><?= e(trans('reports.all_employees')) ?></option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= e((string) $employee['id']) ?>" <?= $employeeId === (int) $employee['id'] ? 'selected' : '' ?>>
                        <?= e($employee['name']) ?> · <?= e($employee['position']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button class="button button--primary" type="submit"><?= e(trans('reports.apply')) ?></button>
    </form>

    <p class="report-filter__summary">
        <strong><?= e(format_month($month)) ?></strong>
        ·
        <?= e($selectedEmployee !== null ? $selectedEmployee['name'] : trans('reports.all_employees')) ?>
    </p>
</section>

<section class="report-summary-grid">
    <article class="report-summary-card report-summary-card--primary">
        <span><?= e(trans('reports.total_net')) ?></span>
        <strong><?= e(format_currency($report['totals']['total_amount'] ?? 0)) ?></strong>
        <small><?= e(trans('reports.total_help', ['count' => $report['totals']['shift_count'] ?? 0])) ?></small>
    </article>
    <article class="report-summary-card">
        <span><?= e(trans('reports.cash')) ?></span>
        <strong><?= e(format_currency($report['totals']['cash_total'] ?? 0)) ?></strong>
        <small><?= e(trans('reports.cash_help')) ?></small>
    </article>
    <article class="report-summary-card">
        <span><?= e(trans('reports.card_net')) ?></span>
        <strong><?= e(format_currency($report['totals']['card_net_total'] ?? 0)) ?></strong>
        <small><?= e(trans('reports.card_help', ['fee' => format_currency($report['totals']['card_fee_total'] ?? 0)])) ?></small>
    </article>
    <article class="report-summary-card report-summary-card--warning">
        <span><?= e(trans('reports.pending')) ?></span>
        <strong><?= e(format_currency($report['totals']['pending_total'] ?? 0)) ?></strong>
        <small><?= e(trans('reports.pending_help')) ?></small>
    </article>
    <article class="report-summary-card report-summary-card--success">
        <span><?= e(trans('reports.paid')) ?></span>
        <strong><?= e(format_currency($report['totals']['paid_total'] ?? 0)) ?></strong>
        <small><?= e(trans('reports.paid_help')) ?></small>
    </article>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('reports.employee_summary_eyebrow')) ?></p>
            <h2><?= e(trans('reports.employee_summary')) ?></h2>
        </div>
        <span class="status-badge">
            <?= e(trans('reports.employee_count', ['count' => count($report['employee_summary'])])) ?>
        </span>
    </div>

    <?php if ($report['employee_summary'] === []): ?>
        <div class="empty-state empty-state--compact">
            <strong><?= e(trans('reports.empty')) ?></strong>
            <p><?= e(trans('reports.empty_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('reports.employee')) ?></th>
                    <th><?= e(trans('reports.shifts')) ?></th>
                    <th><?= e(trans('reports.cash')) ?></th>
                    <th><?= e(trans('reports.card_gross')) ?></th>
                    <th><?= e(trans('reports.card_fee')) ?></th>
                    <th><?= e(trans('reports.card_net')) ?></th>
                    <th><?= e(trans('reports.total_net')) ?></th>
                    <th><?= e(trans('reports.pending')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($report['employee_summary'] as $summary): ?>
                    <tr>
                        <td data-label="<?= e(trans('reports.employee')) ?>">
                            <div class="person-cell">
                                <span class="avatar avatar--small"><?= e(text_initial($summary['name'])) ?></span>
                                <span>
                                    <strong><?= e($summary['name']) ?></strong>
                                    <small><?= e($summary['position']) ?></small>
                                </span>
                            </div>
                        </td>
                        <td data-label="<?= e(trans('reports.shifts')) ?>"><?= e((string) $summary['shift_count']) ?></td>
                        <td data-label="<?= e(trans('reports.cash')) ?>"><?= e(format_currency($summary['cash_total'])) ?></td>
                        <td data-label="<?= e(trans('reports.card_gross')) ?>"><?= e(format_currency($summary['card_gross_total'])) ?></td>
                        <td data-label="<?= e(trans('reports.card_fee')) ?>">- <?= e(format_currency($summary['card_fee_total'])) ?></td>
                        <td data-label="<?= e(trans('reports.card_net')) ?>"><?= e(format_currency($summary['card_net_total'])) ?></td>
                        <td data-label="<?= e(trans('reports.total_net')) ?>"><strong><?= e(format_currency($summary['total_amount'])) ?></strong></td>
                        <td data-label="<?= e(trans('reports.pending')) ?>">
                            <span class="status-pill <?= (float) $summary['pending_total'] > 0 ? 'status-pill--open' : 'status-pill--settled' ?>">
                                <?= e(format_currency($summary['pending_total'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('reports.details_eyebrow')) ?></p>
            <h2><?= e(trans('reports.details')) ?></h2>
        </div>
        <span class="status-badge"><?= e(trans('reports.row_count', ['count' => count($report['details'])])) ?></span>
    </div>

    <?php if ($report['details'] === []): ?>
        <div class="empty-state empty-state--compact">
            <strong><?= e(trans('reports.empty')) ?></strong>
            <p><?= e(trans('reports.empty_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('reports.date')) ?></th>
                    <th><?= e(trans('reports.shift')) ?></th>
                    <th><?= e(trans('reports.employee')) ?></th>
                    <th><?= e(trans('reports.cash')) ?></th>
                    <th><?= e(trans('reports.card_net')) ?></th>
                    <th><?= e(trans('reports.total_net')) ?></th>
                    <th><?= e(trans('reports.status')) ?></th>
                    <th class="table-actions-heading"><?= e(trans('common.actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($report['details'] as $row): ?>
                    <tr>
                        <td data-label="<?= e(trans('reports.date')) ?>"><?= e(format_date($row['shift_date'])) ?></td>
                        <td data-label="<?= e(trans('reports.shift')) ?>"><?= e(trans('shifts.type.' . $row['shift_type'])) ?></td>
                        <td data-label="<?= e(trans('reports.employee')) ?>">
                            <strong><?= e($row['name']) ?></strong>
                            <small class="table-subtext"><?= e($row['position']) ?></small>
                        </td>
                        <td data-label="<?= e(trans('reports.cash')) ?>"><?= e(format_currency($row['cash_amount'])) ?></td>
                        <td data-label="<?= e(trans('reports.card_net')) ?>">
                            <?= e(format_currency($row['card_net_amount'])) ?>
                            <small class="table-subtext">
                                <?= e(trans('reports.fee_applied', ['fee' => format_currency($row['card_fee_amount'])])) ?>
                            </small>
                        </td>
                        <td data-label="<?= e(trans('reports.total_net')) ?>"><strong><?= e(format_currency($row['total_amount'])) ?></strong></td>
                        <td data-label="<?= e(trans('reports.status')) ?>">
                            <span class="status-pill status-pill--<?= e($row['payment_status'] === 'paid' ? 'settled' : ($row['payment_status'] === 'partial' ? 'partially_settled' : 'open')) ?>">
                                <?= e(trans('reports.status.' . $row['payment_status'])) ?>
                            </span>
                        </td>
                        <td data-label="<?= e(trans('common.actions')) ?>">
                            <a class="button button--outline button--small" href="<?= e(url('/entries/' . $row['entry_id'])) ?>">
                                <?= e(trans('common.view')) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('reports.payments_eyebrow')) ?></p>
            <h2><?= e(trans('reports.payments')) ?></h2>
        </div>
        <span class="status-badge"><?= e(trans('reports.payment_count', ['count' => count($report['settlements'])])) ?></span>
    </div>

    <?php if ($report['settlements'] === []): ?>
        <div class="empty-state empty-state--compact">
            <strong><?= e(trans('reports.no_payments')) ?></strong>
            <p><?= e(trans('reports.no_payments_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('reports.payment_date')) ?></th>
                    <th><?= e(trans('reports.settlement')) ?></th>
                    <th><?= e(trans('reports.employee')) ?></th>
                    <th><?= e(trans('reports.cash')) ?></th>
                    <th><?= e(trans('reports.card_net')) ?></th>
                    <th><?= e(trans('reports.paid')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($report['settlements'] as $payment): ?>
                    <tr>
                        <td data-label="<?= e(trans('reports.payment_date')) ?>"><?= e(format_date($payment['payment_date'])) ?></td>
                        <td data-label="<?= e(trans('reports.settlement')) ?>">
                            <a class="text-link" href="<?= e(url('/settlements/' . $payment['id'])) ?>">
                                <?= e(trans('settlements.type.' . $payment['settlement_type'])) ?>
                            </a>
                        </td>
                        <td data-label="<?= e(trans('reports.employee')) ?>"><?= e($payment['name']) ?></td>
                        <td data-label="<?= e(trans('reports.cash')) ?>"><?= e(format_currency($payment['cash_amount'])) ?></td>
                        <td data-label="<?= e(trans('reports.card_net')) ?>"><?= e(format_currency($payment['card_net_amount'])) ?></td>
                        <td data-label="<?= e(trans('reports.paid')) ?>"><strong><?= e(format_currency($payment['total_amount'])) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
