<?php if (!empty($success)): ?>
    <div class="alert alert--success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endif; ?>

<section class="page-header page-header--compact">
    <div>
        <p class="eyebrow"><?= e(trans('settings.eyebrow')) ?></p>
        <h1><?= e(trans('settings.title')) ?></h1>
        <p><?= e(trans('settings.subtitle')) ?></p>
    </div>
    <span class="status-badge status-badge--neutral"><?= e(trans('settings.saved_automatically_note')) ?></span>
</section>

<div class="settings-layout">
    <aside class="settings-nav" aria-label="<?= e(trans('settings.sections')) ?>">
        <a class="settings-nav__item <?= $activeSection === 'restaurant' ? 'settings-nav__item--active' : '' ?>" href="#restaurant-settings">
            <span class="settings-nav__icon">⌂</span>
            <span>
                <strong><?= e(trans('settings.restaurant.title')) ?></strong>
                <small><?= e(trans('settings.restaurant.nav_help')) ?></small>
            </span>
        </a>
        <a class="settings-nav__item <?= $activeSection === 'company' ? 'settings-nav__item--active' : '' ?>" href="#company-settings">
            <span class="settings-nav__icon">▦</span>
            <span>
                <strong><?= e(trans('settings.company.title')) ?></strong>
                <small><?= e(trans('settings.company.nav_help')) ?></small>
            </span>
        </a>
        <a class="settings-nav__item <?= $activeSection === 'profile' ? 'settings-nav__item--active' : '' ?>" href="#profile-settings">
            <span class="settings-nav__icon">◎</span>
            <span>
                <strong><?= e(trans('settings.profile.title')) ?></strong>
                <small><?= e(trans('settings.profile.nav_help')) ?></small>
            </span>
        </a>
        <a class="settings-nav__item <?= $activeSection === 'password' ? 'settings-nav__item--active' : '' ?>" href="#password-settings">
            <span class="settings-nav__icon">◇</span>
            <span>
                <strong><?= e(trans('settings.password.title')) ?></strong>
                <small><?= e(trans('settings.password.nav_help')) ?></small>
            </span>
        </a>
    </aside>

    <div class="settings-content">
        <section class="settings-card" id="restaurant-settings">
            <div class="settings-card__header">
                <div>
                    <span class="eyebrow"><?= e(trans('settings.restaurant.eyebrow')) ?></span>
                    <h2><?= e(trans('settings.restaurant.title')) ?></h2>
                    <p><?= e(trans('settings.restaurant.subtitle')) ?></p>
                </div>
                <span class="settings-card__badge">EUR</span>
            </div>

            <?php if ($restaurantErrors !== []): ?>
                <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
            <?php endif; ?>

            <form class="form form--wide" method="POST" action="<?= e(url('/settings/restaurant')) ?>">
                <?= csrf_field() ?>

                <div class="form-grid">
                    <label class="field field--full">
                        <span><?= e(trans('settings.restaurant.name')) ?></span>
                        <input name="name" type="text" maxlength="120" value="<?= e((string) ($restaurant['name'] ?? '')) ?>" required>
                        <?php if (isset($restaurantErrors['name'])): ?>
                            <small class="field-error"><?= e($restaurantErrors['name']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.restaurant.card_fee')) ?></span>
                        <div class="input-suffix">
                            <input name="default_card_fee" type="number" min="0" max="100" step="0.01" value="<?= e((string) ($restaurant['default_card_fee'] ?? '25.00')) ?>" required>
                            <span>%</span>
                        </div>
                        <small class="field-help"><?= e(trans('settings.restaurant.card_fee_help')) ?></small>
                        <?php if (isset($restaurantErrors['default_card_fee'])): ?>
                            <small class="field-error"><?= e($restaurantErrors['default_card_fee']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.restaurant.first_closing_day')) ?></span>
                        <input name="first_half_closing_day" type="number" min="1" max="28" value="<?= e((string) ($restaurant['first_half_closing_day'] ?? 15)) ?>" required>
                        <small class="field-help"><?= e(trans('settings.restaurant.first_closing_day_help')) ?></small>
                        <?php if (isset($restaurantErrors['first_half_closing_day'])): ?>
                            <small class="field-error"><?= e($restaurantErrors['first_half_closing_day']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.restaurant.timezone')) ?></span>
                        <select name="timezone" required>
                            <?php foreach ($timezones as $timezone): ?>
                                <option value="<?= e($timezone) ?>" <?= ($restaurant['timezone'] ?? '') === $timezone ? 'selected' : '' ?>>
                                    <?= e($timezone) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($restaurantErrors['timezone'])): ?>
                            <small class="field-error"><?= e($restaurantErrors['timezone']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.restaurant.default_language')) ?></span>
                        <select name="default_language" required>
                            <option value="pt" <?= ($restaurant['default_language'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português</option>
                            <option value="en" <?= ($restaurant['default_language'] ?? 'pt') === 'en' ? 'selected' : '' ?>>English</option>
                        </select>
                        <small class="field-help"><?= e(trans('settings.restaurant.default_language_help')) ?></small>
                        <?php if (isset($restaurantErrors['default_language'])): ?>
                            <small class="field-error"><?= e($restaurantErrors['default_language']) ?></small>
                        <?php endif; ?>
                    </label>

                    <div class="field">
                        <span><?= e(trans('settings.restaurant.month_end')) ?></span>
                        <div class="readonly-field"><?= e(trans('settings.restaurant.last_day')) ?></div>
                        <small class="field-help"><?= e(trans('settings.restaurant.month_end_help')) ?></small>
                    </div>

                    <div class="field">
                        <span><?= e(trans('settings.restaurant.currency')) ?></span>
                        <div class="readonly-field">Euro (EUR)</div>
                        <small class="field-help"><?= e(trans('settings.restaurant.currency_help')) ?></small>
                    </div>
                </div>

                <label class="toggle-row">
                    <span>
                        <strong><?= e(trans('settings.restaurant.password_recovery')) ?></strong>
                        <small><?= e(trans('settings.restaurant.password_recovery_help')) ?></small>
                    </span>
                    <input name="password_reset_enabled" type="checkbox" value="1" <?= (int) ($restaurant['password_reset_enabled'] ?? 1) === 1 ? 'checked' : '' ?>>
                    <span class="toggle-control" aria-hidden="true"></span>
                </label>

                <div class="form-actions">
                    <button class="button button--primary" type="submit"><?= e(trans('settings.restaurant.save')) ?></button>
                </div>
            </form>
        </section>

        <section class="settings-card" id="company-settings">
            <div class="settings-card__header">
                <div>
                    <span class="eyebrow"><?= e(trans('settings.company.eyebrow')) ?></span>
                    <h2><?= e(trans('settings.company.title')) ?></h2>
                    <p><?= e(trans('settings.company.subtitle')) ?></p>
                </div>
                <span class="settings-card__icon-large">▦</span>
            </div>

            <?php if ($companyErrors !== []): ?>
                <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
            <?php endif; ?>

            <form class="form form--wide" method="POST" action="<?= e(url('/settings/company')) ?>">
                <?= csrf_field() ?>

                <div class="form-grid">
                    <label class="field">
                        <span><?= e(trans('registration.legal_name')) ?></span>
                        <input name="legal_name" type="text" maxlength="160" value="<?= e((string) ($restaurant['legal_name'] ?? $restaurant['name'] ?? '')) ?>" required>
                        <?php if (isset($companyErrors['legal_name'])): ?><small class="field-error"><?= e($companyErrors['legal_name']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.trade_name')) ?></span>
                        <input name="trade_name" type="text" maxlength="160" value="<?= e((string) ($restaurant['trade_name'] ?? '')) ?>">
                        <?php if (isset($companyErrors['trade_name'])): ?><small class="field-error"><?= e($companyErrors['trade_name']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.country')) ?></span>
                        <select name="country_code" required>
                            <?php foreach ($countries as $code => $country): ?>
                                <option value="<?= e($code) ?>" <?= ($restaurant['country_code'] ?? 'PT') === $code ? 'selected' : '' ?>>
                                    <?= e($country[\App\Core\Locale::current()] ?? $country['en']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($companyErrors['country_code'])): ?><small class="field-error"><?= e($companyErrors['country_code']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.business_type')) ?></span>
                        <select name="business_type" required>
                            <?php foreach (['restaurant', 'cafe', 'bar', 'hotel', 'other'] as $type): ?>
                                <option value="<?= e($type) ?>" <?= ($restaurant['business_type'] ?? 'restaurant') === $type ? 'selected' : '' ?>>
                                    <?= e(trans('registration.business_type.' . $type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($companyErrors['business_type'])): ?><small class="field-error"><?= e($companyErrors['business_type']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.registration_number')) ?></span>
                        <input name="company_registration_number" type="text" maxlength="80" value="<?= e((string) ($restaurant['company_registration_number'] ?? '')) ?>" required>
                        <?php if (isset($companyErrors['company_registration_number'])): ?><small class="field-error"><?= e($companyErrors['company_registration_number']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.vat_number')) ?></span>
                        <input name="vat_number" type="text" maxlength="40" value="<?= e((string) ($restaurant['vat_number'] ?? '')) ?>">
                        <?php if (isset($companyErrors['vat_number'])): ?><small class="field-error"><?= e($companyErrors['vat_number']) ?></small><?php endif; ?>
                    </label>

                    <label class="field field--full">
                        <span><?= e(trans('registration.address_line1')) ?></span>
                        <input name="address_line1" type="text" maxlength="190" value="<?= e((string) ($restaurant['address_line1'] ?? '')) ?>" required>
                        <?php if (isset($companyErrors['address_line1'])): ?><small class="field-error"><?= e($companyErrors['address_line1']) ?></small><?php endif; ?>
                    </label>

                    <label class="field field--full">
                        <span><?= e(trans('registration.address_line2')) ?></span>
                        <input name="address_line2" type="text" maxlength="190" value="<?= e((string) ($restaurant['address_line2'] ?? '')) ?>">
                        <?php if (isset($companyErrors['address_line2'])): ?><small class="field-error"><?= e($companyErrors['address_line2']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.postal_code')) ?></span>
                        <input name="postal_code" type="text" maxlength="32" value="<?= e((string) ($restaurant['postal_code'] ?? '')) ?>" required>
                        <?php if (isset($companyErrors['postal_code'])): ?><small class="field-error"><?= e($companyErrors['postal_code']) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('registration.city')) ?></span>
                        <input name="city" type="text" maxlength="120" value="<?= e((string) ($restaurant['city'] ?? '')) ?>" required>
                        <?php if (isset($companyErrors['city'])): ?><small class="field-error"><?= e($companyErrors['city']) ?></small><?php endif; ?>
                    </label>
                </div>

                <div class="security-note">
                    <span>i</span>
                    <p><?= e(trans('settings.company.validation_note')) ?></p>
                </div>

                <div class="form-actions">
                    <button class="button button--primary" type="submit"><?= e(trans('settings.company.save')) ?></button>
                </div>
            </form>
        </section>

        <section class="settings-card" id="profile-settings">
            <div class="settings-card__header">
                <div>
                    <span class="eyebrow"><?= e(trans('settings.profile.eyebrow')) ?></span>
                    <h2><?= e(trans('settings.profile.title')) ?></h2>
                    <p><?= e(trans('settings.profile.subtitle')) ?></p>
                </div>
                <span class="avatar avatar--large"><?= e(text_initial((string) ($profile['name'] ?? 'U'))) ?></span>
            </div>

            <?php if ($profileErrors !== []): ?>
                <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
            <?php endif; ?>

            <form class="form form--wide" method="POST" action="<?= e(url('/settings/profile')) ?>">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <label class="field">
                        <span><?= e(trans('settings.profile.name')) ?></span>
                        <input name="name" type="text" maxlength="120" value="<?= e((string) ($profile['name'] ?? '')) ?>" required>
                        <?php if (isset($profileErrors['name'])): ?>
                            <small class="field-error"><?= e($profileErrors['name']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.profile.email')) ?></span>
                        <input name="email" type="email" maxlength="190" value="<?= e((string) ($profile['email'] ?? '')) ?>" required>
                        <?php if (isset($profileErrors['email'])): ?>
                            <small class="field-error"><?= e($profileErrors['email']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.profile.language')) ?></span>
                        <select name="language" required>
                            <option value="pt" <?= ($profile['language'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português</option>
                            <option value="en" <?= ($profile['language'] ?? 'pt') === 'en' ? 'selected' : '' ?>>English</option>
                        </select>
                        <small class="field-help"><?= e(trans('settings.profile.language_help')) ?></small>
                        <?php if (isset($profileErrors['language'])): ?>
                            <small class="field-error"><?= e($profileErrors['language']) ?></small>
                        <?php endif; ?>
                    </label>

                    <div class="field">
                        <span><?= e(trans('settings.profile.role')) ?></span>
                        <div class="readonly-field"><?= e(trans('settings.profile.role_manager')) ?></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button button--primary" type="submit"><?= e(trans('settings.profile.save')) ?></button>
                </div>
            </form>
        </section>

        <section class="settings-card" id="password-settings">
            <div class="settings-card__header">
                <div>
                    <span class="eyebrow"><?= e(trans('settings.password.eyebrow')) ?></span>
                    <h2><?= e(trans('settings.password.title')) ?></h2>
                    <p><?= e(trans('settings.password.subtitle')) ?></p>
                </div>
                <span class="settings-card__icon-large">◇</span>
            </div>

            <?php if ($passwordErrors !== []): ?>
                <div class="alert alert--error"><?= e(trans('common.review_fields')) ?></div>
            <?php endif; ?>

            <form class="form form--wide" method="POST" action="<?= e(url('/settings/password')) ?>">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <label class="field field--full">
                        <span><?= e(trans('settings.password.current')) ?></span>
                        <input name="current_password" type="password" autocomplete="current-password" required>
                        <?php if (isset($passwordErrors['current_password'])): ?>
                            <small class="field-error"><?= e($passwordErrors['current_password']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.password.new')) ?></span>
                        <input name="new_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        <?php if (isset($passwordErrors['new_password'])): ?>
                            <small class="field-error"><?= e($passwordErrors['new_password']) ?></small>
                        <?php endif; ?>
                    </label>

                    <label class="field">
                        <span><?= e(trans('settings.password.confirm')) ?></span>
                        <input name="new_password_confirmation" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        <?php if (isset($passwordErrors['new_password_confirmation'])): ?>
                            <small class="field-error"><?= e($passwordErrors['new_password_confirmation']) ?></small>
                        <?php endif; ?>
                    </label>
                </div>

                <div class="security-note">
                    <span>✓</span>
                    <p><?= e(trans('settings.password.help')) ?></p>
                </div>

                <div class="form-actions">
                    <button class="button button--primary" type="submit"><?= e(trans('settings.password.save')) ?></button>
                </div>
            </form>
        </section>
    </div>
</div>
