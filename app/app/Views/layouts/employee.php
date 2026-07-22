<?php
use App\Core\Locale;
$user = auth_user();
$restaurantName = $user['restaurant_name'] ?? 'TipsForMe';
?>
<!DOCTYPE html>
<html lang="<?= e(Locale::current()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TipsForMe - saldo e histórico de gorjetas.">
    <meta name="theme-color" content="#071a2c">
    <meta name="application-name" content="TipsForMe">
    <link rel="manifest" href="<?= e(url('/manifest.webmanifest')) ?>">
    <link rel="icon" href="<?= e(asset('icons/icon.svg')) ?>" type="image/svg+xml">
    <title><?= e(current_section_label()) ?> · <?= e(trans('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css?v=11')) ?>">
</head>
<body class="app-body employee-app">
<a class="skip-link" href="#main-content"><?= e(trans('app.skip_content')) ?></a>
<div class="app-shell">
    <button class="sidebar-backdrop" type="button" aria-label="<?= e(trans('nav.close_menu')) ?>" data-sidebar-backdrop></button>

    <aside class="sidebar sidebar--employee" data-sidebar>
        <div class="sidebar__header">
            <a class="sidebar-brand" href="<?= e(url('/my/dashboard')) ?>">
                <span class="sidebar-brand__mark">t</span>
                <span>tipsforme</span>
            </a>
            <button class="sidebar__close" type="button" aria-label="<?= e(trans('nav.close_menu')) ?>" data-sidebar-close>×</button>
        </div>

        <div class="sidebar-restaurant">
            <span><?= e(text_initial($restaurantName)) ?></span>
            <div>
                <strong><?= e($restaurantName) ?></strong>
                <small><?= e(trans('employee.portal')) ?></small>
            </div>
        </div>

        <span class="sidebar-nav__label"><?= e(trans('employee.portal')) ?></span>
        <nav class="sidebar-nav" aria-label="<?= e(trans('nav.main')) ?>">
            <a class="sidebar-nav__item <?= route_is('/my/dashboard') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/my/dashboard')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('wallet') ?></span>
                <span><?= e(trans('employee.nav.balance')) ?></span>
            </a>
            <a class="sidebar-nav__item <?= route_is('/my/statement') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/my/statement')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('statement') ?></span>
                <span><?= e(trans('employee.nav.statement')) ?></span>
            </a>
            <a class="sidebar-nav__item <?= route_is('/my/payments') ? 'sidebar-nav__item--active' : '' ?>" href="<?= e(url('/my/payments')) ?>">
                <span class="sidebar-nav__icon"><?= nav_icon('check') ?></span>
                <span><?= e(trans('employee.nav.payments')) ?></span>
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
                    <strong><?= e($user['name'] ?? '') ?></strong>
                    <small><?= e($user['employee_position'] ?? $user['email'] ?? '') ?></small>
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
                <span class="desktop-appbar__date"><?= e(date('d/m/Y')) ?></span>
            </div>
        </header>

        <header class="mobile-topbar">
            <button class="mobile-menu-button" type="button" aria-label="<?= e(trans('nav.open_menu')) ?>" aria-expanded="false" data-sidebar-toggle>
                <span></span><span></span><span></span>
            </button>
            <a class="brand" href="<?= e(url('/my/dashboard')) ?>">tipsforme</a>
            <span class="avatar avatar--small"><?= e(text_initial($user['name'] ?? 'U')) ?></span>
        </header>

        <main class="app-content" id="main-content">
            <div class="content-container">
                <?= $content ?>
            </div>
        </main>

        <nav class="mobile-bottom-nav mobile-bottom-nav--employee" aria-label="<?= e(trans('nav.main')) ?>">
            <a class="mobile-bottom-nav__item <?= route_is('/my/dashboard') ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/my/dashboard')) ?>">
                <?= nav_icon('wallet') ?><span><?= e(trans('employee.nav.balance')) ?></span>
            </a>
            <a class="mobile-bottom-nav__item <?= route_is('/my/statement') ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/my/statement')) ?>">
                <?= nav_icon('statement') ?><span><?= e(trans('employee.nav.statement')) ?></span>
            </a>
            <a class="mobile-bottom-nav__item <?= route_is('/my/payments') ? 'mobile-bottom-nav__item--active' : '' ?>" href="<?= e(url('/my/payments')) ?>">
                <?= nav_icon('check') ?><span><?= e(trans('employee.nav.payments')) ?></span>
            </a>
        </nav>
    </div>
</div>
<script src="<?= e(asset('js/app.js?v=11')) ?>" defer></script>
</body>
</html>
