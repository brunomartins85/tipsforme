<?php
$isEdit = $mode === 'edit';
$formAction = $isEdit
    ? url('/shifts/' . $shift['id'] . '/update')
    : url('/shifts');
?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('shifts.eyebrow')) ?></p>
        <h1><?= e($isEdit ? trans('shifts.edit_title') : trans('shifts.create_title')) ?></h1>
        <p><?= e($isEdit ? trans('shifts.edit_subtitle') : trans('shifts.create_subtitle')) ?></p>
    </div>
</section>

<section class="form-card">
    <?php if ($errors !== []): ?>
        <div class="alert alert--error">
            <strong><?= e(trans('common.review_fields')) ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($employees === []): ?>
        <div class="alert alert--warning">
            <?= e(trans('shifts.no_employees')) ?>
            <a class="text-link" href="<?= e(url('/employees/create')) ?>"><?= e(trans('shifts.create_employee')) ?></a>
        </div>
    <?php endif; ?>

    <form class="form form--wide" method="POST" action="<?= e($formAction) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <label class="field">
                <span><?= e(trans('shifts.date')) ?></span>
                <input
                    type="date"
                    name="shift_date"
                    value="<?= e((string) ($shift['shift_date'] ?? '')) ?>"
                    required
                >
                <?php if (isset($errors['shift_date'])): ?>
                    <small class="field-error"><?= e($errors['shift_date']) ?></small>
                <?php endif; ?>
            </label>

            <fieldset class="field fieldset-clean">
                <legend><?= e(trans('shifts.type')) ?></legend>
                <div class="segmented-control">
                    <label>
                        <input type="radio" name="shift_type" value="lunch" <?= ($shift['shift_type'] ?? 'lunch') === 'lunch' ? 'checked' : '' ?>>
                        <span>☀ <?= e(trans('shifts.type.lunch')) ?></span>
                    </label>
                    <label>
                        <input type="radio" name="shift_type" value="dinner" <?= ($shift['shift_type'] ?? '') === 'dinner' ? 'checked' : '' ?>>
                        <span>☾ <?= e(trans('shifts.type.dinner')) ?></span>
                    </label>
                </div>
                <?php if (isset($errors['shift_type'])): ?>
                    <small class="field-error"><?= e($errors['shift_type']) ?></small>
                <?php endif; ?>
            </fieldset>

            <label class="field field--full">
                <span><?= e(trans('shifts.notes')) ?></span>
                <textarea name="notes" maxlength="500" rows="3" placeholder="<?= e(trans('shifts.notes_placeholder')) ?>"><?= e((string) ($shift['notes'] ?? '')) ?></textarea>
                <?php if (isset($errors['notes'])): ?>
                    <small class="field-error"><?= e($errors['notes']) ?></small>
                <?php endif; ?>
            </label>
        </div>

        <div class="employee-selector">
            <div class="employee-selector__header">
                <div>
                    <span class="field-label"><?= e(trans('shifts.select_employees')) ?></span>
                    <p><?= e(trans('shifts.select_employees_help')) ?></p>
                </div>
                <?php if ($employees !== []): ?>
                    <button class="button button--outline button--small" type="button" data-select-all>
                        <?= e(trans('common.select_all')) ?>
                    </button>
                <?php endif; ?>
            </div>

            <?php if (isset($errors['employee_ids'])): ?>
                <small class="field-error field-error--block"><?= e($errors['employee_ids']) ?></small>
            <?php endif; ?>

            <div class="employee-check-grid">
                <?php foreach ($employees as $employee): ?>
                    <?php
                    $employeeId = (int) $employee['id'];
                    $selected = in_array($employeeId, $selectedEmployeeIds, true);
                    $inactive = ($employee['status'] ?? 'active') !== 'active';
                    ?>
                    <label class="employee-check <?= $inactive ? 'employee-check--inactive' : '' ?>">
                        <input
                            type="checkbox"
                            name="employee_ids[]"
                            value="<?= e((string) $employeeId) ?>"
                            data-employee-checkbox
                            <?= $selected ? 'checked' : '' ?>
                            <?= $inactive && !$selected ? 'disabled' : '' ?>
                        >
                        <span class="employee-check__content">
                            <span class="avatar avatar--small"><?= e(text_initial($employee['name'])) ?></span>
                            <span>
                                <strong><?= e($employee['name']) ?></strong>
                                <small><?= e($employee['position']) ?></small>
                            </span>
                            <?php if ($inactive): ?>
                                <em><?= e(trans('employees.status.inactive')) ?></em>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <a class="button button--outline" href="<?= e(url('/shifts')) ?>"><?= e(trans('common.cancel')) ?></a>
            <button class="button button--primary" type="submit" <?= $employees === [] ? 'disabled' : '' ?>>
                <?= e($isEdit ? trans('common.save_changes') : trans('shifts.create')) ?>
            </button>
        </div>
    </form>
</section>
