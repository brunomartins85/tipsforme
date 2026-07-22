<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('employees.eyebrow')) ?></p>
        <h1><?= e(trans('employees.title')) ?></h1>
        <p><?= e(trans('employees.subtitle')) ?></p>
    </div>
    <a class="button button--primary" href="<?= e(url('/employees/create')) ?>">
        <?= e(trans('employees.new')) ?>
    </a>
</section>

<section class="section-card">
    <?php if ($employees === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('employees.empty')) ?></strong>
            <p><?= e(trans('employees.empty_help')) ?></p>
            <a class="button button--primary" href="<?= e(url('/employees/create')) ?>">
                <?= e(trans('employees.create_first')) ?>
            </a>
        </div>
    <?php else: ?>
        <div class="responsive-table">
            <table>
                <thead>
                <tr>
                    <th><?= e(trans('employees.employee')) ?></th>
                    <th><?= e(trans('employees.language')) ?></th>
                    <th><?= e(trans('employees.shifts')) ?></th>
                    <th><?= e(trans('access.title')) ?></th>
                    <th><?= e(trans('employees.status')) ?></th>
                    <th class="table-actions-heading"><?= e(trans('common.actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td data-label="<?= e(trans('employees.employee')) ?>">
                            <div class="person-cell">
                                <span class="avatar avatar--small"><?= e(text_initial($employee['name'])) ?></span>
                                <div>
                                    <strong><?= e($employee['name']) ?></strong>
                                    <span><?= e($employee['position']) ?></span>
                                    <?php if (!empty($employee['email'])): ?>
                                        <small><?= e($employee['email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td data-label="<?= e(trans('employees.language')) ?>"><?= e(strtoupper($employee['language'])) ?></td>
                        <td data-label="<?= e(trans('employees.shifts')) ?>"><?= e((string) $employee['shift_count']) ?></td>
                        <td data-label="<?= e(trans('access.title')) ?>">
                            <?php if (!empty($employee['user_id'])): ?>
                                <span class="status-pill status-pill--settled"><?= e(trans('access.created')) ?></span>
                                <?php if (!empty($employee['last_login_at'])): ?>
                                    <small class="table-subtext"><?= e(trans('access.last_login', ['date' => format_datetime($employee['last_login_at'])])) ?></small>
                                <?php else: ?>
                                    <small class="table-subtext"><?= e(trans('access.awaiting_activation')) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="status-pill status-pill--open"><?= e(trans('access.not_created')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="<?= e(trans('employees.status')) ?>">
                            <span class="status-pill status-pill--<?= e($employee['status']) ?>">
                                <?= e(trans('employees.status.' . $employee['status'])) ?>
                            </span>
                        </td>
                        <td data-label="<?= e(trans('common.actions')) ?>">
                            <div class="table-actions">
                                <?php if ($employee['status'] === 'active' && !empty($employee['email'])): ?>
                                    <form method="POST" action="<?= e(url('/employees/' . $employee['id'] . '/send-access')) ?>">
                                        <?= csrf_field() ?>
                                        <button class="button button--primary button--small" type="submit">
                                            <?= e(!empty($employee['user_id']) ? trans('access.resend') : trans('access.create')) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <a class="button button--outline button--small" href="<?= e(url('/employees/' . $employee['id'] . '/edit')) ?>">
                                    <?= e(trans('common.edit')) ?>
                                </a>
                                <form method="POST" action="<?= e(url('/employees/' . $employee['id'] . '/toggle-status')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="button button--ghost button--small" type="submit">
                                        <?= e($employee['status'] === 'active' ? trans('common.deactivate') : trans('common.activate')) ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
