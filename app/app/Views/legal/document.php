<section class="legal-page">
    <?php if (auth_user() === null && in_array($document, ['terms', 'privacy', 'cookies'], true)): ?>
        <div class="page-back-action">
            <a class="button button--outline" href="<?= e(url('/login')) ?>">← <?= e(trans('common.back_login')) ?></a>
        </div>
    <?php endif; ?>
    <header class="legal-page__header">
        <p class="eyebrow"><?= e(trans('legal.eyebrow')) ?></p>
        <h1><?= e($documentContent['title'] ?? '') ?></h1>
        <p><?= e($documentContent['intro'] ?? '') ?></p>
        <span><?= e(trans('legal.updated', ['date' => $updatedAt])) ?></span>
    </header>

    <div class="legal-page__content">
        <?php foreach (($documentContent['sections'] ?? []) as $section): ?>
            <section class="legal-section">
                <h2><?= e($section['title']) ?></h2>
                <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>

        <section class="legal-contact-card">
            <h2><?= e(trans('legal.contact_title')) ?></h2>
            <p><?= e(trans('legal.contact_help')) ?></p>
            <a class="button button--outline" href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>

            <?php if ($document === 'data-rights' && auth_user() !== null): ?>
                <a class="button button--primary" href="<?= e(url('/data-rights/request')) ?>"><?= e(trans('data_request.open')) ?></a>
            <?php endif; ?>
        </section>

        
    </div>
</section>
