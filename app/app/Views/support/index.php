<section class="support-page">
    <?php if (auth_user() === null): ?>
        <div class="page-back-action">
            <a class="button button--outline" href="<?= e(url('/login')) ?>">← <?= e(trans('common.back_login')) ?></a>
        </div>
    <?php endif; ?>
    <div class="support-hero">
        <span class="support-hero__glow"></span>
        <p class="eyebrow"><?= e(trans('support.eyebrow')) ?></p>
        <h1><?= e(trans('support.title')) ?></h1>
        <p><?= e(trans('support.subtitle')) ?></p>
        <div class="support-badge"><?= e(trans('support.voluntary')) ?></div>
    </div>

    <div class="support-grid">
        <article class="support-card support-card--iban">
            <div class="support-card__icon">€</div>
            <span><?= e(trans('support.bank_transfer')) ?></span>
            <h2>IBAN</h2>
            <code id="support-iban"><?= e($iban) ?></code>
            <button class="button button--outline button--full" type="button" data-copy-target="support-iban" data-copy-success="<?= e(trans('support.copied')) ?>">
                <?= e(trans('support.copy_iban')) ?>
            </button>
        </article>

        <article class="support-card">
            <div class="support-card__icon">P</div>
            <span><?= e(trans('support.online')) ?></span>
            <h2>PayPal</h2>
            <p><?= e(trans('support.paypal_help')) ?></p>
            <a class="button button--primary button--full" href="<?= e($paypalUrl) ?>" target="_blank" rel="noopener noreferrer">
                <?= e(trans('support.open_paypal')) ?>
            </a>
        </article>

        <article class="support-card">
            <div class="support-card__icon">S</div>
            <span><?= e(trans('support.secure_payment')) ?></span>
            <h2>Stripe</h2>
            <p><?= e(trans('support.stripe_help')) ?></p>
            <a class="button button--primary button--full" href="<?= e($stripeUrl) ?>" target="_blank" rel="noopener noreferrer">
                <?= e(trans('support.open_stripe')) ?>
            </a>
        </article>
    </div>

    <div class="support-note">
        <strong><?= e(trans('support.transparency_title')) ?></strong>
        <p><?= e(trans('support.transparency_help')) ?></p>
    </div>
</section>
