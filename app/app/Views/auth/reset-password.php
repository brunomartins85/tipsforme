<?php
use App\Core\Locale;
?>
<main class="auth-shell">
    <div class="auth-language">
        <a href="?lang=<?= Locale::current() === 'pt' ? 'en' : 'pt' ?>">
            <?= Locale::current() === 'pt' ? 'English' : 'Português' ?>
        </a>
    </div>

    <section class="login-card" aria-labelledby="reset-title">
        <div class="brand login-card__brand">tipsforme</div>

        <div class="login-card__heading">
            <h1 id="reset-title"><?= e(trans('password.reset_title')) ?></h1>
            <p><?= e($record !== null ? trans('password.reset_subtitle') : trans('password.invalid_token')) ?></p>
        </div>

        <?php if (isset($errors['token'])): ?>
            <div class="alert alert--error" role="alert"><?= e($errors['token']) ?></div>
        <?php endif; ?>

        <?php if ($record === null): ?>
            <a class="button button--primary button--full" href="<?= e(url('/forgot-password')) ?>">
                <?= e(trans('password.request_new')) ?>
            </a>
        <?php else: ?>
            <form class="form" method="POST" action="<?= e(url('/reset-password')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <label class="field">
                    <span><?= e(trans('password.new')) ?></span>
                    <div class="password-field">
                        <input id="new-password" type="password" name="password" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" data-password-toggle="new-password" data-show-label="<?= e(trans('auth.show')) ?>" data-hide-label="<?= e(trans('auth.hide')) ?>">
                            <?= e(trans('auth.show')) ?>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <small class="field-error"><?= e($errors['password']) ?></small>
                    <?php endif; ?>
                </label>

                <label class="field">
                    <span><?= e(trans('password.confirm')) ?></span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    <?php if (isset($errors['password_confirmation'])): ?>
                        <small class="field-error"><?= e($errors['password_confirmation']) ?></small>
                    <?php endif; ?>
                </label>

                <button class="button button--primary button--full" type="submit">
                    <?= e(trans('password.save')) ?>
                </button>
            </form>
        <?php endif; ?>
    </section>
</main>
