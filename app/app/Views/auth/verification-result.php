<div class="auth-shell">
    <section class="login-card" aria-labelledby="verification-title">
        <div class="brand login-card__brand">tipsforme</div>
        <div class="verification-symbol verification-symbol--error">!</div>
        <div class="login-card__heading">
            <h1 id="verification-title"><?= e(trans('registration.invalid_link_title')) ?></h1>
            <p><?= e(trans('registration.invalid_link_message')) ?></p>
        </div>
        <a class="button button--primary button--full" href="<?= e(url('/login')) ?>"><?= e(trans('password.back_login')) ?></a>
    </section>
</div>
