<?php
use App\Core\Locale;
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
    <title><?= e(trans('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css?v=11')) ?>">
</head>
<body class="auth-body">
    <a class="skip-link" href="#main-content"><?= e(trans('app.skip_content')) ?></a>
    <main id="main-content">
        <?= $content ?>
    </main>
    <script src="<?= e(asset('js/app.js?v=11')) ?>" defer></script>
</body>
</html>
