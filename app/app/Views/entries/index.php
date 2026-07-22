<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('entries.eyebrow')) ?></p>
        <h1><?= e(trans('entries.title')) ?></h1>
        <p><?= e(trans('entries.subtitle')) ?></p>
    </div>
    <a class="button button--primary" href="<?= e(url('/entries/create')) ?>">
        <?= e(trans('entries.new')) ?>
    </a>
</section>

<section class="section-card">
    <?php if ($entries === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('entries.empty')) ?></strong>
            <p><?= e(trans('entries.empty_help')) ?></p>
            <a class="button button--primary" href="<?= e(url('/entries/create')) ?>">
                <?= e(trans('entries.create_first')) ?>
            </a>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('entries.shift')) ?></th>
                    <th><?= e(trans('entries.cash')) ?></th>
                    <th><?= e(trans('entries.card_net')) ?></th>
                    <th><?= e(trans('entries.total_distributed')) ?></th>
                    <th><?= e(trans('entries.employees')) ?></th>
                    <th><?= e(trans('entries.status')) ?></th>
                    <th class="table-actions-heading"><?= e(trans('common.actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td data-label="<?= e(trans('entries.shift')) ?>">
                            <strong><?= e(format_date($entry['shift_date'])) ?></strong>
                            <small class="table-subtext"><?= e(trans('shifts.type.' . $entry['shift_type'])) ?></small>
                        </td>
                        <td data-label="<?= e(trans('entries.cash')) ?>">
                            <?= e(format_currency($entry['cash_amount'])) ?>
                        </td>
                        <td data-label="<?= e(trans('entries.card_net')) ?>">
                            <?= e(format_currency($entry['card_net_amount'])) ?>
                            <small class="table-subtext">
                                <?= e(trans('entries.fee_short', [
                                    'fee' => format_currency($entry['card_fee_amount']),
                                    'percentage' => format_percentage($entry['card_fee_percentage']),
                                ])) ?>
                            </small>
                        </td>
                        <td data-label="<?= e(trans('entries.total_distributed')) ?>">
                            <strong><?= e(format_currency($entry['total_net_amount'])) ?></strong>
                        </td>
                        <td data-label="<?= e(trans('entries.employees')) ?>">
                            <?= e(trans('entries.people_count', ['count' => $entry['employee_count']])) ?>
                        </td>
                        <td data-label="<?= e(trans('entries.status')) ?>">
                            <span class="status-pill status-pill--<?= e($entry['status']) ?>">
                                <?= e(trans('entries.status.' . $entry['status'])) ?>
                            </span>
                        </td>
                        <td data-label="<?= e(trans('common.actions')) ?>">
                            <div class="table-actions">
                                <a class="button button--outline button--small" href="<?= e(url('/entries/' . $entry['id'])) ?>">
                                    <?= e(trans('common.view')) ?>
                                </a>
                                <?php if ($entry['status'] === 'open'): ?>
                                    <a class="button button--ghost button--small" href="<?= e(url('/entries/' . $entry['id'] . '/edit')) ?>">
                                        <?= e(trans('common.edit')) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
