<section class="page-header page-header--compact">
    <div>
        <p class="eyebrow"><?= e(trans('data_request.eyebrow')) ?></p>
        <h1><?= e(trans('data_request.title')) ?></h1>
        <p><?= e(trans('data_request.subtitle')) ?></p>
    </div>
</section>

<?php if (!empty($success)): ?><div class="alert alert--success"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>

<section class="card data-request-card">
    <form class="form form--wide" method="POST" action="<?= e(url('/data-rights/request')) ?>">
        <?= csrf_field() ?>

        <label class="field">
            <span><?= e(trans('data_request.type')) ?></span>
            <select name="request_type" required>
                <?php foreach (['access', 'export', 'correction', 'deletion', 'restriction', 'objection'] as $type): ?>
                    <option value="<?= e($type) ?>"><?= e(trans('data_request.type.' . $type)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span><?= e(trans('data_request.details')) ?></span>
            <textarea name="details" maxlength="2000" rows="6" placeholder="<?= e(trans('data_request.details_placeholder')) ?>"></textarea>
        </label>

        <div class="security-note">
            <span>i</span>
            <p><?= e(trans('data_request.identity_note')) ?></p>
        </div>

        <button class="button button--primary" type="submit"><?= e(trans('data_request.submit')) ?></button>
    </form>
</section>
