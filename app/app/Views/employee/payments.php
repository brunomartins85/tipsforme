<section class="page-header">
    <div>
        <p class="eyebrow"><?= e(trans('employee.payments.eyebrow')) ?></p>
        <h1><?= e(trans('employee.payments.title')) ?></h1>
        <p><?= e(trans('employee.payments.subtitle')) ?></p>
    </div>
</section>

<section class="section-card">
    <?php if ($payments === []): ?>
        <div class="empty-state">
            <strong><?= e(trans('employee.no_payments')) ?></strong>
            <p><?= e(trans('employee.no_payments_help')) ?></p>
        </div>
    <?php else: ?>
        <div class="payment-history-grid">
            <?php foreach ($payments as $payment): ?>
                <article class="payment-history-card">
                    <div class="payment-history-card__header">
                        <div>
                            <span class="eyebrow"><?= e(trans('settlements.type.' . $payment['settlement_type'])) ?></span>
                            <strong><?= e(format_month(substr($payment['reference_month'], 0, 7))) ?></strong>
                        </div>
                        <span class="status-pill status-pill--settled"><?= e(trans('employee.status.paid')) ?></span>
                    </div>
                    <div class="payment-history-card__amount"><?= e(format_currency($payment['total_amount'])) ?></div>
                    <dl>
                        <div><dt><?= e(trans('settlements.payment_date')) ?></dt><dd><?= e(format_date($payment['payment_date'])) ?></dd></div>
                        <div><dt><?= e(trans('entries.cash')) ?></dt><dd><?= e(format_currency($payment['cash_amount'])) ?></dd></div>
                        <div><dt><?= e(trans('entries.card_net')) ?></dt><dd><?= e(format_currency($payment['card_net_amount'])) ?></dd></div>
                        <div><dt><?= e(trans('entries.fee')) ?></dt><dd>- <?= e(format_currency($payment['card_fee_amount'])) ?></dd></div>
                    </dl>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
