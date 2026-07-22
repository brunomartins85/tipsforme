<div class="onboarding-shell">
    <section class="onboarding-card" aria-labelledby="onboarding-title">
        <div class="onboarding-progress">
            <span class="onboarding-progress__item onboarding-progress__item--done">✓</span>
            <i></i>
            <span class="onboarding-progress__item onboarding-progress__item--active">2</span>
            <i></i>
            <span class="onboarding-progress__item">3</span>
        </div>

        <div class="registration-card__intro">
            <p class="eyebrow"><?= e(trans('onboarding.eyebrow')) ?></p>
            <h1 id="onboarding-title"><?= e(trans('onboarding.title')) ?></h1>
            <p><?= e(trans('onboarding.subtitle')) ?></p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
        <?php endif; ?>

        <form class="form" method="POST" action="<?= e(url('/onboarding')) ?>">
            <?= csrf_field() ?>

            <div class="form-grid">
                <label class="field">
                    <span><?= e(trans('settings.restaurant.card_fee')) ?></span>
                    <div class="input-suffix">
                        <input name="default_card_fee" type="number" min="0" max="100" step="0.01" value="<?= e((string) ($restaurant['default_card_fee'] ?? '25.00')) ?>" required>
                        <span>%</span>
                    </div>
                    <small class="field-help"><?= e(trans('onboarding.card_fee_help')) ?></small>
                    <?php if (isset($errors['default_card_fee'])): ?><small class="field-error"><?= e($errors['default_card_fee']) ?></small><?php endif; ?>
                </label>

                <label class="field">
                    <span><?= e(trans('settings.restaurant.first_closing_day')) ?></span>
                    <input name="first_half_closing_day" type="number" min="1" max="28" value="<?= e((string) ($restaurant['first_half_closing_day'] ?? 15)) ?>" required>
                    <small class="field-help"><?= e(trans('onboarding.closing_day_help')) ?></small>
                    <?php if (isset($errors['first_half_closing_day'])): ?><small class="field-error"><?= e($errors['first_half_closing_day']) ?></small><?php endif; ?>
                </label>

                <label class="field">
                    <span><?= e(trans('settings.restaurant.timezone')) ?></span>
                    <select name="timezone" required>
                        <?php foreach (timezone_identifiers_list() as $timezone): ?>
                            <?php if (!str_starts_with($timezone, 'Europe/') && !in_array($timezone, ['Atlantic/Reykjavik', 'Asia/Nicosia'], true)) continue; ?>
                            <option value="<?= e($timezone) ?>" <?= ($restaurant['timezone'] ?? 'Europe/Lisbon') === $timezone ? 'selected' : '' ?>><?= e($timezone) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['timezone'])): ?><small class="field-error"><?= e($errors['timezone']) ?></small><?php endif; ?>
                </label>

                <label class="field">
                    <span><?= e(trans('settings.restaurant.default_language')) ?></span>
                    <select name="default_language" required>
                        <option value="pt" <?= ($restaurant['default_language'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português</option>
                        <option value="en" <?= ($restaurant['default_language'] ?? 'pt') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </label>
            </div>

            <div class="onboarding-summary">
                <strong><?= e(trans('onboarding.next_title')) ?></strong>
                <p><?= e(trans('onboarding.next_help')) ?></p>
            </div>

            <button class="button button--primary button--full button--large" type="submit"><?= e(trans('onboarding.finish')) ?></button>
        </form>
    </section>
</div>
