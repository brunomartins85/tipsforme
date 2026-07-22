<?php
use App\Core\Locale;
?>
<main class="auth-shell">
    <div class="auth-language">
        <a href="?lang=<?= Locale::current() === 'pt' ? 'en' : 'pt' ?>">
            <?= Locale::current() === 'pt' ? 'English' : 'Português' ?>
        </a>
    </div>

    <section class="login-card" aria-labelledby="forgot-title">
        <div class="brand login-card__brand">tipsforme</div>

        <div class="login-card__heading">
            <h1 id="forgot-title"><?= e(trans('password.forgot_title')) ?></h1>
            <p><?= e(trans('password.forgot_subtitle')) ?></p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert--success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form" method="POST" action="<?= e(url('/forgot-password')) ?>" novalidate>
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

            <button class="button button--primary button--full" type="submit">
                <?= e(trans('password.send_link')) ?>
            </button>
            <a class="button button--outline button--full" href="<?= e(url('/login')) ?>">
                <?= e(trans('password.back_login')) ?>
            </a>
        </form>
    </section>
</main>
