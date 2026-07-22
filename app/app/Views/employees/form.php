<?php
$isEdit = $mode === 'edit';
$formAction = $isEdit
    ? url('/employees/' . $employee['id'] . '/update')
    : url('/employees');
?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('employees.eyebrow')) ?></p>
        <h1><?= e($isEdit ? trans('employees.edit_title') : trans('employees.create_title')) ?></h1>
        <p><?= e($isEdit ? trans('employees.edit_subtitle') : trans('employees.create_subtitle')) ?></p>
    </div>
</section>

<section class="form-card">
    <?php if ($errors !== []): ?>
        <div class="alert alert--error">
            <strong><?= e(trans('common.review_fields')) ?></strong>
        </div>
    <?php endif; ?>

    <form class="form form--wide" method="POST" action="<?= e($formAction) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid">
            <label class="field field--full">
                <span><?= e(trans('employees.name')) ?></span>
                <input
                    type="text"
                    name="name"
                    maxlength="120"
                    value="<?= e((string) ($employee['name'] ?? '')) ?>"
                    autocomplete="name"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <small class="field-error"><?= e($errors['name']) ?></small>
                <?php endif; ?>
            </label>

            <label class="field">
                <span><?= e(trans('employees.position')) ?></span>
                <input
                    type="text"
                    name="position"
                    maxlength="80"
                    value="<?= e((string) ($employee['position'] ?? '')) ?>"
                    placeholder="<?= e(trans('employees.position_placeholder')) ?>"
                    required
                >
                <?php if (isset($errors['position'])): ?>
                    <small class="field-error"><?= e($errors['position']) ?></small>
                <?php endif; ?>
            </label>

            <label class="field">
                <span><?= e(trans('employees.email')) ?></span>
                <input
                    type="email"
                    name="email"
                    maxlength="190"
                    value="<?= e((string) ($employee['email'] ?? '')) ?>"
                    autocomplete="email"
                    placeholder="nome@exemplo.com"
                >
                <small class="field-help"><?= e(trans('employees.email_help')) ?></small>
                <?php if (isset($errors['email'])): ?>
                    <small class="field-error"><?= e($errors['email']) ?></small>
                <?php endif; ?>
            </label>

            <label class="field">
                <span><?= e(trans('employees.language')) ?></span>
                <select name="language" required>
                    <option value="pt" <?= ($employee['language'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português</option>
                    <option value="en" <?= ($employee['language'] ?? 'pt') === 'en' ? 'selected' : '' ?>>English</option>
                </select>
                <?php if (isset($errors['language'])): ?>
                    <small class="field-error"><?= e($errors['language']) ?></small>
                <?php endif; ?>
            </label>

            <?php if ($isEdit): ?>
                <div class="field">
                    <span><?= e(trans('employees.status')) ?></span>
                    <div class="readonly-field">
                        <span class="status-pill status-pill--<?= e($employee['status']) ?>">
                            <?= e(trans('employees.status.' . $employee['status'])) ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <a class="button button--outline" href="<?= e(url('/employees')) ?>"><?= e(trans('common.cancel')) ?></a>
            <button class="button button--primary" type="submit">
                <?= e($isEdit ? trans('common.save_changes') : trans('employees.create')) ?>
            </button>
        </div>
    </form>
</section>
