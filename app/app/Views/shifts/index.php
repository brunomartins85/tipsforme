<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('shifts.eyebrow')) ?></p>
        <h1><?= e(trans('shifts.title')) ?></h1>
        <p><?= e(trans('shifts.subtitle')) ?></p>
    </div>
    <a class="button button--primary" href="<?= e(url('/shifts/create')) ?>">
        <?= e(trans('shifts.new')) ?>
    </a>
</section>

<section class="section-card">
    <?php if ($shifts === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('shifts.empty')) ?></strong>
            <p><?= e(trans('shifts.empty_help')) ?></p>
            <a class="button button--primary" href="<?= e(url('/shifts/create')) ?>">
                <?= e(trans('shifts.create_first')) ?>
            </a>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('shifts.date')) ?></th>
                    <th><?= e(trans('shifts.type')) ?></th>
                    <th><?= e(trans('shifts.employees')) ?></th>
                    <th><?= e(trans('shifts.status')) ?></th>
                    <th class="table-actions-heading"><?= e(trans('common.actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($shifts as $shift): ?>
                    <tr>
                        <td data-label="<?= e(trans('shifts.date')) ?>">
                            <strong><?= e(format_date($shift['shift_date'])) ?></strong>
                        </td>
                        <td data-label="<?= e(trans('shifts.type')) ?>">
                            <span class="shift-type shift-type--<?= e($shift['shift_type']) ?>">
                                <?= e(trans('shifts.type.' . $shift['shift_type'])) ?>
                            </span>
                        </td>
                        <td data-label="<?= e(trans('shifts.employees')) ?>">
                            <strong><?= e(trans('shifts.people_count', ['count' => $shift['employee_count']])) ?></strong>
                            <small class="table-subtext"><?= e($shift['employee_names'] ?? '') ?></small>
                        </td>
                        <td data-label="<?= e(trans('shifts.status')) ?>">
                            <span class="status-pill status-pill--<?= e($shift['status']) ?>">
                                <?= e(trans('shifts.status.' . $shift['status'])) ?>
                            </span>
                        </td>
                        <td data-label="<?= e(trans('common.actions')) ?>">
                            <div class="table-actions">
                                <?php if ($shift['status'] === 'open'): ?>
                                    <a class="button button--primary button--small" href="<?= e(url('/entries/create?shift_id=' . $shift['id'])) ?>">
                                        <?= e(trans('shifts.add_entry')) ?>
                                    </a>
                                    <a class="button button--outline button--small" href="<?= e(url('/shifts/' . $shift['id'] . '/edit')) ?>">
                                        <?= e(trans('common.edit')) ?>
                                    </a>
                                    <form method="POST" action="<?= e(url('/shifts/' . $shift['id'] . '/delete')) ?>" data-confirm="<?= e(trans('shifts.confirm_delete')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="button button--danger-ghost button--small" type="submit">
                                            <?= e(trans('common.delete')) ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <?php if (!empty($shift['tip_entry_id'])): ?>
                                        <a class="button button--outline button--small" href="<?= e(url('/entries/' . $shift['tip_entry_id'])) ?>">
                                            <?= e(trans('shifts.view_entry')) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="muted-text"><?= e(trans('shifts.closed_locked')) ?></span>
                                    <?php endif; ?>
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
