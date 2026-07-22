<?php
$isEdit = $mode === 'edit';
$formAction = $isEdit
    ? url('/entries/' . $entry['id'] . '/update')
    : url('/entries');
$selectedShiftId = (int) ($entry['shift_id'] ?? 0);
$selectedShift = null;

foreach ($availableShifts as $availableShift) {
    if ((int) $availableShift['id'] === $selectedShiftId) {
        $selectedShift = $availableShift;
        break;
    }
}

$employeeCount = $isEdit
    ? (int) ($entry['employee_count'] ?? 0)
    : (int) ($selectedShift['employee_count'] ?? 0);
?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('entries.eyebrow')) ?></p>
        <h1><?= e($isEdit ? trans('entries.edit_title') : trans('entries.create_title')) ?></h1>
        <p><?= e($isEdit ? trans('entries.edit_subtitle') : trans('entries.create_subtitle')) ?></p>
    </div>
</section>

<?php if (!$isEdit && $availableShifts === []): ?>
    <section class="section-card empty-state">
        <strong><?= e(trans('entries.no_available_shifts')) ?></strong>
        <p><?= e(trans('entries.no_available_shifts_help')) ?></p>
        <a class="button button--primary" href="<?= e(url('/shifts/create')) ?>">
            <?= e(trans('entries.create_shift')) ?>
        </a>
    </section>
<?php else: ?>
    <form
        class="entry-layout"
        method="POST"
        action="<?= e($formAction) ?>"
        novalidate
        data-tip-form
        data-fee-percentage="<?= e((string) $feePercentage) ?>"
        data-participant-singular="<?= e(trans('entries.participant_singular')) ?>"
        data-participant-plural="<?= e(trans('entries.participant_plural')) ?>"
    >
        <?= csrf_field() ?>

        <section class="form-card form-card--full">
            <?php if ($errors !== []): ?>
                <div class="alert alert--error">
                    <strong><?= e(trans('common.review_fields')) ?></strong>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['amounts'])): ?>
                <div class="alert alert--error"><?= e($errors['amounts']) ?></div>
            <?php endif; ?>

            <div class="form form--wide">
                <div class="form-grid">
                    <?php if ($isEdit): ?>
                        <div class="field field--full">
                            <span><?= e(trans('entries.shift')) ?></span>
                            <div class="selected-shift-card">
                                <div>
                                    <strong><?= e(format_date((string) $entry['shift_date'])) ?> · <?= e(trans('shifts.type.' . $entry['shift_type'])) ?></strong>
                                    <small><?= e((string) ($entry['employee_names'] ?? '')) ?></small>
                                </div>
                                <span><?= e(trans('entries.people_count', ['count' => $employeeCount])) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <label class="field field--full">
                            <span><?= e(trans('entries.select_shift')) ?></span>
                            <select name="shift_id" required data-shift-select>
                                <?php foreach ($availableShifts as $shift): ?>
                                    <option
                                        value="<?= e((string) $shift['id']) ?>"
                                        data-employee-count="<?= e((string) $shift['employee_count']) ?>"
                                        data-employee-names="<?= e((string) ($shift['employee_names'] ?? '')) ?>"
                                        <?= (int) $shift['id'] === $selectedShiftId ? 'selected' : '' ?>
                                    >
                                        <?= e(format_date($shift['shift_date'])) ?> ·
                                        <?= e(trans('shifts.type.' . $shift['shift_type'])) ?> ·
                                        <?= e(trans('entries.people_count', ['count' => $shift['employee_count']])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['shift_id'])): ?>
                                <small class="field-error"><?= e($errors['shift_id']) ?></small>
                            <?php endif; ?>
                        </label>

                        <div class="field field--full">
                            <div class="selected-shift-card selected-shift-card--soft">
                                <div>
                                    <span class="field-label"><?= e(trans('entries.participants')) ?></span>
                                    <small data-shift-employee-names><?= e((string) ($selectedShift['employee_names'] ?? '')) ?></small>
                                </div>
                                <strong data-shift-employee-count><?= e(trans('entries.people_count', ['count' => $employeeCount])) ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <label class="field money-field">
                        <span><?= e(trans('entries.cash_amount')) ?></span>
                        <div class="money-input">
                            <span>€</span>
                            <input
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                name="cash_amount"
                                value="<?= e((string) ($entry['cash_amount_input'] ?? '')) ?>"
                                placeholder="0,00"
                                data-cash-input
                            >
                        </div>
                        <?php if (isset($errors['cash_amount'])): ?>
                            <small class="field-error"><?= e($errors['cash_amount']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field money-field">
                        <span><?= e(trans('entries.card_gross_amount')) ?></span>
                        <div class="money-input">
                            <span>€</span>
                            <input
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                name="card_gross_amount"
                                value="<?= e((string) ($entry['card_gross_amount_input'] ?? '')) ?>"
                                placeholder="0,00"
                                data-card-input
                            >
                        </div>
                        <?php if (isset($errors['card_gross_amount'])): ?>
                            <small class="field-error"><?= e($errors['card_gross_amount']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field field--full">
                        <span><?= e(trans('entries.notes')) ?></span>
                        <textarea name="notes" maxlength="500" rows="3" placeholder="<?= e(trans('entries.notes_placeholder')) ?>"><?= e((string) ($entry['notes'] ?? '')) ?></textarea>
                        <?php if (isset($errors['notes'])): ?>
                            <small class="field-error"><?= e($errors['notes']) ?></small>
                        <?php endif; ?>
                    </label>
                </div>

                <div class="form-actions">
                    <a class="button button--outline" href="<?= e($isEdit ? url('/entries/' . $entry['id']) : url('/entries')) ?>">
                        <?= e(trans('common.cancel')) ?>
                    </a>
                    <button class="button button--primary" type="submit">
                        <?= e($isEdit ? trans('common.save_changes') : trans('entries.create')) ?>
                    </button>
                </div>
            </div>
        </section>

        <aside class="calculation-card" aria-live="polite">
            <p class="eyebrow"><?= e(trans('entries.preview')) ?></p>
            <h2><?= e(trans('entries.distribution_preview')) ?></h2>

            <dl class="calculation-list">
                <div>
                    <dt><?= e(trans('entries.cash')) ?></dt>
                    <dd data-preview-cash>€ 0,00</dd>
                </div>
                <div>
                    <dt><?= e(trans('entries.card_gross')) ?></dt>
                    <dd data-preview-card-gross>€ 0,00</dd>
                </div>
                <div class="calculation-list__fee">
                    <dt><?= e(trans('entries.card_fee', ['percentage' => format_percentage($feePercentage)])) ?></dt>
                    <dd data-preview-fee>- € 0,00</dd>
                </div>
                <div>
                    <dt><?= e(trans('entries.card_net')) ?></dt>
                    <dd data-preview-card-net>€ 0,00</dd>
                </div>
                <div class="calculation-list__total">
                    <dt><?= e(trans('entries.total_distributed')) ?></dt>
                    <dd data-preview-total>€ 0,00</dd>
                </div>
            </dl>

            <div class="per-person-preview">
                <span><?= e(trans('entries.approx_per_employee')) ?></span>
                <strong data-preview-person>€ 0,00</strong>
                <small data-preview-participants>
                    <?= e(trans('entries.people_count', ['count' => $employeeCount])) ?>
                </small>
            </div>

            <p class="calculation-note"><?= e(trans('entries.rounding_note')) ?></p>
        </aside>
    </form>
<?php endif; ?>
