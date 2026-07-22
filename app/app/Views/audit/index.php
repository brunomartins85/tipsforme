<section class="page-header page-header--compact">
    <div>
        <p class="eyebrow"><?= e(trans('audit.eyebrow')) ?></p>
        <h1><?= e(trans('audit.title')) ?></h1>
        <p><?= e(trans('audit.subtitle')) ?></p>
    </div>

    <form class="audit-filter" method="GET" action="<?= e(url('/audit')) ?>">
        <label class="field">
            <span><?= e(trans('audit.filter')) ?></span>
            <select name="action">
                <option value=""><?= e(trans('audit.all_actions')) ?></option>
                <?php foreach ($actions as $action): ?>
                    <option value="<?= e($action) ?>" <?= $selectedAction === $action ? 'selected' : '' ?>>
                        <?= e(trans('audit.action.' . $action)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button button--outline" type="submit"><?= e(trans('common.filter')) ?></button>
    </form>
</section>

<section class="section-card">
    <?php if ($logs === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('audit.empty')) ?></strong>
            <p><?= e(trans('audit.empty_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="audit-timeline">
            <?php foreach ($logs as $log): ?>
                <article class="audit-item">
                    <div class="audit-item__marker"><?= nav_icon('audit') ?></div>
                    <div class="audit-item__content">
                        <div class="audit-item__heading">
                            <div>
                                <strong><?= e(trans('audit.action.' . $log['action'])) ?></strong>
                                <span><?= e($log['user_name'] ?: trans('audit.system_user')) ?></span>
                            </div>
                            <time datetime="<?= e($log['created_at']) ?>"><?= e(format_datetime($log['created_at'])) ?></time>
                        </div>

                        <?php if (!empty($log['description'])): ?>
                            <p><?= e($log['description']) ?></p>
                        <?php endif; ?>

                        <div class="audit-item__meta">
                            <?php if (!empty($log['entity_type'])): ?>
                                <span><?= e(trans('audit.entity')) ?>: <?= e($log['entity_type']) ?><?= $log['entity_id'] ? ' #' . e((string) $log['entity_id']) : '' ?></span>
                            <?php endif; ?>
                            <?php if (!empty($log['ip_address'])): ?>
                                <span>IP: <?= e($log['ip_address']) ?></span>
                            <?php endif; ?>
                            <?php foreach ($log['metadata_decoded'] as $key => $value): ?>
                                <?php if (is_scalar($value)): ?>
                                    <span><?= e((string) $key) ?>: <?= e((string) $value) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
