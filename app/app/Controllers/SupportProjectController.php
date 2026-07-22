<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class SupportProjectController
{
    public function index(): void
    {
        $user = auth_user();
        $layout = $user === null
            ? 'auth'
            : (($user['role'] ?? '') === 'employee' ? 'employee' : 'app');

        View::render('support/index', [
            'iban' => (string) env('SUPPORT_IBAN', 'PT50 0018 0003 6397 7136 0205 8'),
            'paypalUrl' => (string) env('SUPPORT_PAYPAL_URL', 'https://www.paypal.com/paypalme/brunocmartins85'),
            'stripeUrl' => (string) env('SUPPORT_STRIPE_URL', 'https://donate.stripe.com/3cIdR872X6FA1XJh1XgQE00'),
        ], $layout);
    }
}
