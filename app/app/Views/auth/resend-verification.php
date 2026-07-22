<div class="auth-shell">
    <section class="login-card" aria-labelledby="resend-title">
        <div class="brand login-card__brand">tipsforme</div>
        <div class="login-card__heading">
            <h1 id="resend-title"><?= e(trans('registration.resend_title')) ?></h1>
            <p><?= e(trans('registration.resend_help')) ?></p>
        </div>

        <?php if (!empty($success)): ?><div class="alert alert--success"><?= e($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>

        <form class="form" method="POST" action="<?= e(url('/verify-email/resend')) ?>">
            <?= csrf_field() ?>
            <label class="field">
                <span><?= e(trans('auth.email')) ?></span>
                <input name="email" type="email" maxlength="190" value="<?= e($email) ?>" autocomplete="email" required>
            </label>
            <button class="button button--primary button--full" type="submit"><?= e(trans('registration.resend')) ?></button>
        </form>

        <a class="button button--outline button--full" href="<?= e(url('/login')) ?>"><?= e(trans('password.back_login')) ?></a>
    </section>
</div>
