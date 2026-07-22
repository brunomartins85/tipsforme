<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('entries.eyebrow')) ?></p>
        <h1><?= e(trans('entries.details_title')) ?></h1>
        <p>
            <?= e(format_date($entry['shift_date'])) ?> ·
            <?= e(trans('shifts.type.' . $entry['shift_type'])) ?>
        </p>
    </div>
    <div class="page-header__actions">
        <a class="button button--outline" href="<?= e(url('/entries')) ?>"><?= e(trans('common.back')) ?></a>
        <?php if ($entry['status'] === 'open'): ?>
            <a class="button button--primary" href="<?= e(url('/entries/' . $entry['id'] . '/edit')) ?>">
                <?= e(trans('common.edit')) ?>
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="summary-grid">
    <article class="summary-card">
        <span><?= e(trans('entries.cash')) ?></span>
        <strong><?= e(format_currency($entry['cash_amount'])) ?></strong>
    </article>
    <article class="summary-card">
        <span><?= e(trans('entries.card_gross')) ?></span>
        <strong><?= e(format_currency($entry['card_gross_amount'])) ?></strong>
    </article>
    <article class="summary-card summary-card--fee">
        <span><?= e(trans('entries.card_fee', ['percentage' => format_percentage($entry['card_fee_percentage'])])) ?></span>
        <strong>- <?= e(format_currency($entry['card_fee_amount'])) ?></strong>
    </article>
    <article class="summary-card summary-card--primary">
        <span><?= e(trans('entries.total_distributed')) ?></span>
        <strong><?= e(format_currency($entry['total_net_amount'])) ?></strong>
    </article>
</section>

<section class="section-card">
    <div class="section-card__header">
        <div>
            <p class="eyebrow"><?= e(trans('entries.distribution')) ?></p>
            <h2><?= e(trans('entries.employee_values')) ?></h2>
        </div>
        <span class="status-pill status-pill--<?= e($entry['status']) ?>">
            <?= e(trans('entries.status.' . $entry['status'])) ?>
        </span>
    </div>

    <div class="responsive-table">
        <table>
            <thead>
            <tr>
                <th><?= e(trans('employees.employee')) ?></th>
                <th><?= e(trans('entries.cash')) ?></th>
                <th><?= e(trans('entries.card_gross')) ?></th>
                <th><?= e(trans('entries.fee')) ?></th>
                <th><?= e(trans('entries.card_net')) ?></th>
                <th><?= e(trans('entries.total')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($distributions as $distribution): ?>
                <tr>
                    <td data-label="<?= e(trans('employees.employee')) ?>">
                        <div class="person-cell">
                            <span class="avatar avatar--small"><?= e(text_initial($distribution['name'])) ?></span>
                            <span>
                                <strong><?= e($distribution['name']) ?></strong>
                                <small><?= e($distribution['position']) ?></small>
                            </span>
                        </div>
                    </td>
                    <td data-label="<?= e(trans('entries.cash')) ?>"><?= e(format_currency($distribution['cash_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.card_gross')) ?>"><?= e(format_currency($distribution['card_gross_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.fee')) ?>">- <?= e(format_currency($distribution['card_fee_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.card_net')) ?>"><?= e(format_currency($distribution['card_net_amount'])) ?></td>
                    <td data-label="<?= e(trans('entries.total')) ?>"><strong><?= e(format_currency($distribution['total_amount'])) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if (!empty($entry['notes'])): ?>
    <section class="section-card">
        <p class="eyebrow"><?= e(trans('entries.notes')) ?></p>
        <p><?= nl2br(e($entry['notes'])) ?></p>
    </section>
<?php endif; ?>

<?php if ($entry['status'] === 'open'): ?>
    <section class="danger-zone">
        <div>
            <strong><?= e(trans('entries.delete_title')) ?></strong>
            <p><?= e(trans('entries.delete_help')) ?></p>
        </div>
        <form method="POST" action="<?= e(url('/entries/' . $entry['id'] . '/delete')) ?>" data-confirm="<?= e(trans('entries.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <button class="button button--danger-ghost" type="submit"><?= e(trans('common.delete')) ?></button>
        </form>
    </section>
<?php endif; ?>
