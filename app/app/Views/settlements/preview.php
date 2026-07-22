<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($errors !== []): ?>
    <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('settlements.preview_eyebrow')) ?></p>
        <h1><?= e(trans('settlements.type.' . $summary['type'])) ?></h1>
        <p><?= e(format_month($summary['reference_month'])) ?> · <?= e(trans('settlements.employee_count', ['count' => count($summary['payments'])])) ?></p>
    </div>
    <a class="button button--outline" href="<?= e(url('/settlements?month=' . $summary['reference_month'])) ?>">
        <?= e(trans('common.back')) ?>
    </a>
</section>

<section class="settlement-period-card">
    <div>
        <span><?= e(trans('settlements.cash_period')) ?></span>
        <strong><?= e(format_date($summary['periods']['cash_start'])) ?> – <?= e(format_date($summary['periods']['cash_end'])) ?></strong>
    </div>
    <div>
        <span><?= e(trans('settlements.card_period')) ?></span>
        <strong>
            <?php if ($summary['periods']['include_card']): ?>
                <?= e(format_date($summary['periods']['card_start'])) ?> – <?= e(format_date($summary['periods']['card_end'])) ?>
            <?php else: ?>
                <?= e(trans('settlements.not_included')) ?>
            <?php endif; ?>
        </strong>
    </div>
</section>

<section class="summary-grid">
    <article class="summary-card">
        <span><?= e(trans('entries.cash')) ?></span>
        <strong><?= e(format_cents($summary['totals']['cash_cents'])) ?></strong>
    </article>
    <article class="summary-card">
        <span><?= e(trans('entries.card_gross')) ?></span>
        <strong><?= e(format_cents($summary['totals']['card_gross_cents'])) ?></strong>
    </article>
    <article class="summary-card summary-card--fee">
        <span><?= e(trans('entries.fee')) ?></span>
        <strong>- <?= e(format_cents($summary['totals']['card_fee_cents'])) ?></strong>
    </article>
    <article class="summary-card summary-card--primary">
        <span><?= e(trans('settlements.total_paid')) ?></span>
        <strong><?= e(format_cents($summary['totals']['total_cents'])) ?></strong>
    </article>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('settlements.payment_preview')) ?></p>
            <h2><?= e(trans('settlements.employee_values')) ?></h2>
        </div>
    </div>

    <?php if ($summary['payments'] === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('settlements.nothing_pending')) ?></strong>
            <p><?= e(trans('settlements.nothing_pending_help')) ?></p>
            <a class="button button--outline" href="<?= e(url('/settlements?month=' . $summary['reference_month'])) ?>">
                <?= e(trans('common.back')) ?>
            </a>
        </div>
    <?php else: ?>
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
                </tr>
                </thead>
                <tbody>
                <?php foreach ($summary['payments'] as $payment): ?>
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
                        <td data-label="<?= e(trans('entries.cash')) ?>"><?= e(format_cents($payment['cash_cents'])) ?></td>
                        <td data-label="<?= e(trans('entries.card_gross')) ?>"><?= e(format_cents($payment['card_gross_cents'])) ?></td>
                        <td data-label="<?= e(trans('entries.fee')) ?>">- <?= e(format_cents($payment['card_fee_cents'])) ?></td>
                        <td data-label="<?= e(trans('entries.card_net')) ?>"><?= e(format_cents($payment['card_net_cents'])) ?></td>
                        <td data-label="<?= e(trans('settlements.total_paid')) ?>"><strong><?= e(format_cents($payment['total_cents'])) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($summary['payments'] !== []): ?>
    <section class="form-card form-card--full settlement-confirm-card">
        <form class="form form--wide" method="POST" action="<?= e(url('/settlements')) ?>" data-confirm="<?= e(trans('settlements.confirm_warning')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="<?= e($summary['type']) ?>">
            <input type="hidden" name="reference_month" value="<?= e($summary['reference_month']) ?>">

            <div class="form-grid">
                <label class="field">
                    <span><?= e(trans('settlements.payment_date')) ?></span>
                    <input type="date" name="payment_date" value="<?= e($paymentDate) ?>" required>
                    <?php if (isset($errors['payment_date'])): ?>
                        <small class="field-error"><?= e($errors['payment_date']) ?></small>
                    <?php endif; ?>
                </label>

                <label class="field field--full">
                    <span><?= e(trans('settlements.notes')) ?></span>
                    <textarea name="notes" maxlength="500" placeholder="<?= e(trans('settlements.notes_placeholder')) ?>"><?= e($notes) ?></textarea>
                    <?php if (isset($errors['notes'])): ?>
                        <small class="field-error"><?= e($errors['notes']) ?></small>
                    <?php endif; ?>
                </label>
            </div>

            <div class="immutable-note">
                <strong><?= e(trans('settlements.immutable_title')) ?></strong>
                <p><?= e(trans('settlements.immutable_help')) ?></p>
            </div>

            <div class="form-actions">
                <a class="button button--outline" href="<?= e(url('/settlements?month=' . $summary['reference_month'])) ?>">
                    <?= e(trans('common.cancel')) ?>
                </a>
                <button class="button button--primary" type="submit">
                    <?= e(trans('settlements.register_payment')) ?>
                </button>
            </div>
        </form>
    </section>
<?php endif; ?>
