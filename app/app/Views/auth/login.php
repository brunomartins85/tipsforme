<?php
use App\Core\Locale;
?>
<main class="auth-shell">
    <div class="auth-language">
        <a href="?lang=<?= Locale::current() === 'pt' ? 'en' : 'pt' ?>">
            <?= Locale::current() === 'pt' ? 'English' : 'Português' ?>
        </a>
    </div>

    <section class="login-card" aria-labelledby="login-title">
        <div class="brand login-card__brand">tipsforme</div>

        <div class="login-card__heading">
            <h1 id="login-title"><?= e(trans('auth.welcome')) ?></h1>
            <p><?= e(trans('auth.subtitle')) ?></p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert--success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form" method="POST" action="<?= e(url('/login')) ?>" novalidate>
            <?= csrf_field() ?>

            <label class="field">
                <span><?= e(trans('auth.email')) ?></span>
                <input
                    type="email"
                    name="email"
                    value="<?= e($email) ?>"
                    placeholder="name@restaurant.com"
                    autocomplete="email"
                    required
                >
            </label>

            <label class="field">
                <span class="field-label-row">
                    <span><?= e(trans('auth.password')) ?></span>
                    <a class="text-link" href="<?= e(url('/forgot-password')) ?>"><?= e(trans('password.forgot')) ?></a>
                </span>
                <div class="password-field">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button class="password-toggle" type="button" data-password-toggle="password" data-show-label="<?= e(trans('auth.show')) ?>" data-hide-label="<?= e(trans('auth.hide')) ?>">
                        <?= e(trans('auth.show')) ?>
                    </button>
                </div>
            </label>

            <button class="button button--primary button--full" type="submit">
                <?= e(trans('auth.login')) ?>
            </button>
        </form>

        <div class="auth-divider"><span><?= e(trans('registration.already_registered')) ?></span></div>
        <a class="button button--outline button--full" href="<?= e(url('/register')) ?>"><?= e(trans('auth.register')) ?></a>
        <a class="auth-secondary-link" href="<?= e(url('/verify-email/resend')) ?>"><?= e(trans('registration.resend_login_link')) ?></a>
        <a class="auth-support-link" href="<?= e(url('/support-project')) ?>">♥ <?= e(trans('nav.support_project')) ?></a>

        <div class="auth-legal-links">
            <a href="<?= e(url('/legal/terms')) ?>"><?= e(trans('legal.terms')) ?></a>
            <a href="<?= e(url('/legal/privacy')) ?>"><?= e(trans('legal.privacy')) ?></a>
            <a href="<?= e(url('/legal/cookies')) ?>"><?= e(trans('legal.cookies')) ?></a>
        </div>
        <p class="login-card__note"><?= e(trans('auth.secure_note')) ?></p>
    </section>
</main>
