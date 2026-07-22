<div class="auth-shell">
    <section class="login-card login-card--wide" aria-labelledby="pending-title">
        <div class="brand login-card__brand">tipsforme</div>
        <div class="verification-symbol">✉</div>

        <div class="login-card__heading">
            <h1 id="pending-title"><?= e(trans('registration.pending_title')) ?></h1>
            <p><?= e(trans('registration.pending_message', ['email' => $email])) ?></p>
        </div>

        <?php if ($emailSent): ?>
            <div class="alert alert--success"><?= e(trans('registration.email_sent')) ?></div>
        <?php else: ?>
            <div class="alert alert--warning"><?= e(trans('registration.email_failed')) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?><div class="alert alert--success"><?= e($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>

        <form class="form" method="POST" action="<?= e(url('/verify-email/resend')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= e($email) ?>">
            <button class="button button--outline button--full" type="submit"><?= e(trans('registration.resend')) ?></button>
        </form>

        <a class="button button--primary button--full" href="<?= e(url('/login')) ?>"><?= e(trans('password.back_login')) ?></a>
    </section>
</div>
