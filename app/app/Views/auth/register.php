<?php
use App\Core\Locale;
?>
<div class="registration-shell">
    <header class="public-header">
        <a class="sidebar-brand public-brand" href="<?= e(url('/login')) ?>">
            <span class="sidebar-brand__mark">t</span>
            <span>tipsforme</span>
        </a>
        <div class="public-header__actions">
            <a class="text-link" href="?lang=<?= Locale::current() === 'pt' ? 'en' : 'pt' ?>">
                <?= Locale::current() === 'pt' ? 'English' : 'Português' ?>
            </a>
            <a class="button button--outline button--small" href="<?= e(url('/login')) ?>"><?= e(trans('registration.have_account')) ?></a>
        </div>
    </header>

    <section class="registration-card" aria-labelledby="registration-title">
        <div class="registration-card__intro">
            <p class="eyebrow"><?= e(trans('registration.eyebrow')) ?></p>
            <h1 id="registration-title"><?= e(trans('registration.title')) ?></h1>
            <p><?= e(trans('registration.subtitle')) ?></p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
        <?php endif; ?>

        <?php if ($message = flash('error')): ?>
            <div class="alert alert--error"><?= e($message) ?></div>
        <?php endif; ?>

        <form class="form registration-form" method="POST" action="<?= e(url('/register')) ?>" novalidate>
            <?= csrf_field() ?>
            <input class="honeypot-field" type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true">

            <fieldset class="registration-section">
                <legend>
                    <span>1</span>
                    <div>
                        <strong><?= e(trans('registration.company_section')) ?></strong>
                        <small><?= e(trans('registration.company_section_help')) ?></small>
                    </div>
                </legend>

                <div class="form-grid">
                    <label class="field">
                        <span><?= e(trans('registration.legal_name')) ?> *</span>
                        <input name="legal_name" type="text" maxlength="160" value="<?= e($old['legal_name'] ?? '') ?>" autocomplete="organization" required>
                        <?php if (isset($errors['legal_name'])): ?><small class="field-error"><?= e($errors['legal_name']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.trade_name')) ?></span>
                        <input name="trade_name" type="text" maxlength="160" value="<?= e($old['trade_name'] ?? '') ?>">
                        <?php if (isset($errors['trade_name'])): ?><small class="field-error"><?= e($errors['trade_name']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.country')) ?> *</span>
                        <select name="country_code" required>
                            <?php foreach ($countries as $code => $country): ?>
                                <option value="<?= e($code) ?>" <?= ($old['country_code'] ?? 'PT') === $code ? 'selected' : '' ?>>
                                    <?= e($country[Locale::current()] ?? $country['en']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['country_code'])): ?><small class="field-error"><?= e($errors['country_code']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.business_type')) ?> *</span>
                        <select name="business_type" required>
                            <?php foreach (['restaurant', 'cafe', 'bar', 'hotel', 'other'] as $type): ?>
                                <option value="<?= e($type) ?>" <?= ($old['business_type'] ?? 'restaurant') === $type ? 'selected' : '' ?>>
                                    <?= e(trans('registration.business_type.' . $type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['business_type'])): ?><small class="field-error"><?= e($errors['business_type']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.registration_number')) ?> *</span>
                        <input name="company_registration_number" type="text" maxlength="80" value="<?= e($old['company_registration_number'] ?? '') ?>" required>
                        <small class="field-help"><?= e(trans('registration.registration_number_help')) ?></small>
                        <?php if (isset($errors['company_registration_number'])): ?><small class="field-error"><?= e($errors['company_registration_number']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.vat_number')) ?></span>
                        <input name="vat_number" type="text" maxlength="40" value="<?= e($old['vat_number'] ?? '') ?>" placeholder="PT123456789">
                        <small class="field-help"><?= e(trans('registration.vat_help')) ?></small>
                        <?php if (isset($errors['vat_number'])): ?><small class="field-error"><?= e($errors['vat_number']) ?></small><?php endif; ?>
                    </label>
                </div>

                <div class="registration-info">
                    <span><?= nav_icon('audit') ?></span>
                    <p>
                        <?= e(trans('registration.eu_registry_note')) ?>
                        <a href="https://e-justice.europa.eu/topics/registers-business-insolvency-land/business-registers-search-company-eu_en" target="_blank" rel="noopener"><?= e(trans('registration.bris_link')) ?></a>
                        ·
                        <a href="https://europa.eu/youreurope/business/taxation/vat/check-vat-number-vies/index_en.htm" target="_blank" rel="noopener"><?= e(trans('registration.vies_link')) ?></a>
                    </p>
                </div>
            </fieldset>

            <fieldset class="registration-section">
                <legend>
                    <span>2</span>
                    <div>
                        <strong><?= e(trans('registration.address_section')) ?></strong>
                        <small><?= e(trans('registration.address_section_help')) ?></small>
                    </div>
                </legend>

                <div class="form-grid">
                    <label class="field field--full">
                        <span><?= e(trans('registration.address_line1')) ?> *</span>
                        <input name="address_line1" type="text" maxlength="190" value="<?= e($old['address_line1'] ?? '') ?>" autocomplete="address-line1" required>
                        <?php if (isset($errors['address_line1'])): ?><small class="field-error"><?= e($errors['address_line1']) ?></small><?php endif; ?>
                    </label>

                    <label class="field field--full">
                        <span><?= e(trans('registration.address_line2')) ?></span>
                        <input name="address_line2" type="text" maxlength="190" value="<?= e($old['address_line2'] ?? '') ?>" autocomplete="address-line2">
                        <?php if (isset($errors['address_line2'])): ?><small class="field-error"><?= e($errors['address_line2']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.postal_code')) ?> *</span>
                        <input name="postal_code" type="text" maxlength="32" value="<?= e($old['postal_code'] ?? '') ?>" autocomplete="postal-code" required>
                        <?php if (isset($errors['postal_code'])): ?><small class="field-error"><?= e($errors['postal_code']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.city')) ?> *</span>
                        <input name="city" type="text" maxlength="120" value="<?= e($old['city'] ?? '') ?>" autocomplete="address-level2" required>
                        <?php if (isset($errors['city'])): ?><small class="field-error"><?= e($errors['city']) ?></small><?php endif; ?>
                    </label>
                </div>
            </fieldset>

            <fieldset class="registration-section">
                <legend>
                    <span>3</span>
                    <div>
                        <strong><?= e(trans('registration.admin_section')) ?></strong>
                        <small><?= e(trans('registration.admin_section_help')) ?></small>
                    </div>
                </legend>

                <div class="form-grid">
                    <label class="field">
                        <span><?= e(trans('registration.admin_name')) ?> *</span>
                        <input name="admin_name" type="text" maxlength="120" value="<?= e($old['admin_name'] ?? '') ?>" autocomplete="name" required>
                        <?php if (isset($errors['admin_name'])): ?><small class="field-error"><?= e($errors['admin_name']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.admin_email')) ?> *</span>
                        <input name="admin_email" type="email" maxlength="190" value="<?= e($old['admin_email'] ?? '') ?>" autocomplete="email" required>
                        <?php if (isset($errors['admin_email'])): ?><small class="field-error"><?= e($errors['admin_email']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.password')) ?> *</span>
                        <input id="register-password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        <?php if (isset($errors['password'])): ?><small class="field-error"><?= e($errors['password']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.password_confirmation')) ?> *</span>
                        <input name="password_confirmation" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        <?php if (isset($errors['password_confirmation'])): ?><small class="field-error"><?= e($errors['password_confirmation']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.language')) ?> *</span>
                        <select name="language" required>
                            <option value="pt" <?= ($old['language'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português</option>
                            <option value="en" <?= ($old['language'] ?? 'pt') === 'en' ? 'selected' : '' ?>>English</option>
                        </select>
                    </label>
                </div>
            </fieldset>

            <fieldset class="registration-section registration-section--consent">
                <legend>
                    <span>4</span>
                    <div>
                        <strong><?= e(trans('registration.legal_section')) ?></strong>
                        <small><?= e(trans('registration.legal_section_help')) ?></small>
                    </div>
                </legend>

                <label class="consent-row">
                    <input name="terms_accepted" type="checkbox" value="1" <?= !empty($old['terms_accepted']) ? 'checked' : '' ?> required>
                    <span>
                        <?= trans('registration.accept_terms', [
                            'url' => e(url('/legal/terms')),
                            'version' => e($termsVersion),
                        ]) ?>
                    </span>
                </label>
                <?php if (isset($errors['terms_accepted'])): ?><small class="field-error"><?= e($errors['terms_accepted']) ?></small><?php endif; ?>

                <label class="consent-row">
                    <input name="privacy_acknowledged" type="checkbox" value="1" <?= !empty($old['privacy_acknowledged']) ? 'checked' : '' ?> required>
                    <span>
                        <?= trans('registration.accept_privacy', [
                            'url' => e(url('/legal/privacy')),
                            'version' => e($privacyVersion),
                        ]) ?>
                    </span>
                </label>
                <?php if (isset($errors['privacy_acknowledged'])): ?><small class="field-error"><?= e($errors['privacy_acknowledged']) ?></small><?php endif; ?>

                <label class="consent-row consent-row--optional">
                    <input name="marketing_consent" type="checkbox" value="1" <?= !empty($old['marketing_consent']) ? 'checked' : '' ?>>
                    <span>
                        <?= e(trans('registration.marketing_consent')) ?>
                        <small><?= e(trans('registration.marketing_optional')) ?></small>
                    </span>
                </label>
            </fieldset>

            <button class="button button--primary button--full button--large" type="submit">
                <?= e(trans('registration.create_account')) ?>
            </button>

            <p class="registration-form__footer">
                <?= e(trans('registration.already_registered')) ?>
                <a class="text-link" href="<?= e(url('/login')) ?>"><?= e(trans('auth.login')) ?></a>
            </p>
        </form>
    </section>
</div>
