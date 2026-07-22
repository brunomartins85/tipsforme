<?php
use App\Core\Locale;

$user = auth_user();
$restaurantName = $user['restaurant_name'] ?? 'TipsForMe';
$clockLocale = Locale::current() === 'en' ? 'en-GB' : 'pt-PT';
$clockTimezone = $user['restaurant_timezone'] ?? date_default_timezone_get();
$weekdayNames = Locale::current() === 'en'
    ? [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    : [1 => 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
$headerClock = $weekdayNames[(int) date('N')] . ', ' . date('H:i') . "'";
?>
<!DOCTYPE html>
<html lang="<?= e(Locale::current()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TipsForMe - gestão simples e transparente de gorjetas.">
    <meta name="theme-color" content="#071a2c">
    <meta name="application-name" content="TipsForMe">
    <link rel="manifest" href="<?= e(url('/manifest.webmanifest')) ?>">
    <link rel="icon" href="<?= e(asset('icons/icon.svg')) ?>" type="image/svg+xml">
    <title><?= e(current_section_label()) ?> · <?= e(trans('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css?v=12')) ?>">
</head>
<body class="app-body">
<a class="skip-link" href="#main-content"><?= e(trans('app.skip_content')) ?></a>
<div class="app-shell">
    <button class="sidebar-backdrop" type="button" aria-label="<?= e(trans('nav.close_menu')) ?>" data-sidebar-backdrop></button>

    <aside class="sidebar" data-sidebar>
        <div class="sidebar__header">
            <a class="sidebar-brand" href="<?= e(url('/dashboard')) ?>">
                <span class="sidebar-brand__mark">t</span>
                <span>tipsforme</span>
            </a>
            <button class="sidebar__close" type="button" aria-label="<?= e(trans('nav.close_menu')) ?>" data-sidebar-close>×</button>
        </div>

        <span class="sidebar-nav__label"><?= e(trans('nav.workspace')) ?></span>
        <nav class="sidebar-nav" aria-label="<?= e(trans('nav.main')) ?>">
            <a class="sidebar-nav__item <?= route_is('/dashboard') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/dashboard')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('dashboard') ?></span>
                <span><?= e(trans('nav.dashboard')) ?></span>
            </a>
            <a class="sidebar-nav__item <?= route_is('/employees') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/employees')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('employees') ?></span>
                <span><?= e(trans('nav.employees')) ?></span>
            </a>
            <a class="sidebar-nav__item <?= (route_is('/shifts') || route_is('/entries')) ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/shifts')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('shifts') ?></span>
                <span><?= e(trans('nav.operations')) ?></span>
            </a>
            <a class="sidebar-nav__item <?= route_is('/settlements') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/settlements')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('settlements') ?></span>
                <span><?= e(trans('nav.payments')) ?></span>
            </a>
        </nav>

        <span class="sidebar-nav__label sidebar-nav__label--account"><?= e(trans('nav.account')) ?></span>
        <nav class="sidebar-nav" aria-label="<?= e(trans('nav.account')) ?>">
            <a class="sidebar-nav__item <?= route_is('/settings') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/settings')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('settings') ?></span>
                <span><?= e(trans('nav.settings')) ?></span>
            </a>
            <a class="sidebar-nav__item sidebar-nav__item--support <?= route_is('/support-project') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/support-project')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('support') ?></span>
                <span><?= e(trans('nav.support_project')) ?></span>
                <i>♥</i>
            </a>
        </nav>

        <div class="sidebar__footer">
            <div class="sidebar-user">
                <span class="avatar avatar--small"><?= e(text_initial($user['name'] ?? 'U')) ?></span>
                <div>
                    <strong><?= e($restaurantName) ?></strong>
                    <span class="sidebar-user__name"><?= e($user['name'] ?? '') ?></span>
                    <small><?= e($user['email'] ?? '') ?></small>
                </div>
            </div>

            <div class="sidebar-legal-links">
                <a href="<?= e(url('/legal/privacy')) ?>"><?= e(trans('legal.privacy')) ?></a>
                <a href="<?= e(url('/legal/terms')) ?>"><?= e(trans('legal.terms')) ?></a>
            </div>
            <div class="sidebar-version"><?= e(trans('app.version', ['version' => app_version()])) ?></div>

            <div class="sidebar-footer-actions">
                <a class="button button--sidebar button--small" href="?lang=<?= Locale::current() === 'pt' ? 'en' : 'pt' ?>">
                    <?= Locale::current() === 'pt' ? 'EN' : 'PT' ?>
                </a>
                <form method="POST" action="<?= e(url('/logout')) ?>">
                    <?= csrf_field() ?>
                    <button class="button button--sidebar button--small" type="submit"><?= e(trans('nav.logout')) ?></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="app-main">
        <header class="desktop-appbar">
            <div>
                <span><?= e(trans('nav.current_section')) ?></span>
                <strong><?= e(current_section_label()) ?></strong>
            </div>
            <div class="desktop-appbar__right">
                <span
                    class="online-indicator app-clock"
                    data-live-clock
                    data-timezone="<?= e($clockTimezone) ?>"
                    data-locale="<?= e($clockLocale) ?>"
                >
                    <i></i>
                    <span data-live-clock-value><?= e($headerClock) ?></span>
                </span>
                <span class="desktop-appbar__date"><?= e(date('d/m/Y')) ?></span>
            </div>
        </header>

        <header class="mobile-topbar">
            <button class="mobile-menu-button" type="button" aria-label="<?= e(trans('nav.open_menu')) ?>" aria-expanded="false" data-sidebar-toggle>
                <span></span><span></span><span></span>
            </button>
            <a class="brand" href="<?= e(url('/dashboard')) ?>">tipsforme</a>
            <span class="avatar avatar--small"><?= e(text_initial($user['name'] ?? 'U')) ?></span>
        </header>

        <main class="app-content" id="main-content">
            <div class="content-container">
                <?= $content ?>
            </div>
        </main>

        <nav class="mobile-bottom-nav" aria-label="<?= e(trans('nav.main')) ?>">
            <a class="mobile-bottom-nav__item <?= route_is('/dashboard') ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/dashboard')) ?>">
                <?= nav_icon('dashboard') ?><span><?= e(trans('nav.dashboard')) ?></span>
            </a>
            <a class="mobile-bottom-nav__item <?= route_is('/employees') ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/employees')) ?>">
                <?= nav_icon('employees') ?><span><?= e(trans('nav.employees')) ?></span>
            </a>
            <a class="mobile-bottom-nav__item <?= (route_is('/shifts') || route_is('/entries')) ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/shifts')) ?>">
                <?= nav_icon('shifts') ?><span><?= e(trans('nav.operations_short')) ?></span>
            </a>
            <a class="mobile-bottom-nav__item <?= route_is('/settlements') ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/settlements')) ?>">
                <?= nav_icon('settlements') ?><span><?= e(trans('nav.payments_short')) ?></span>
            </a>
        </nav>
    </div>
</div>

<script src="<?= e(asset('js/app.js?v=12')) ?>" defer></script>
</body>
</html>
