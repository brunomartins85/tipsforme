<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\DataRequest;
use App\Services\AuditLogger;

final class DataRequestController
{
    public function create(): void
    {
        $user = auth_user();
        $layout = ($user['role'] ?? '') === 'employee' ? 'employee' : 'app';

        View::render('legal/request', [
            'success' => flash('success'),
            'error' => flash('error'),
        ], $layout);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/data-rights/request');
        }

        $user = auth_user();
        $type = trim((string) ($_POST['request_type'] ?? ''));
        $details = trim((string) ($_POST['details'] ?? ''));
        $allowed = ['access', 'export', 'correction', 'deletion', 'restriction', 'objection'];

        if (!in_array($type, $allowed, true) || text_length($details) > 2000) {
            flash('error', trans('data_request.invalid'));
            redirect('/data-rights/request');
        }

        $requestId = (new DataRequest())->create(
            (int) $user['restaurant_id'],
            (int) $user['id'],
            $type,
            $details !== '' ? $details : null
        );

        AuditLogger::record('privacy.data_request_created', 'data_request', $requestId, null, [
            'request_type' => $type,
        ]);

        flash('success', trans('data_request.created'));
        redirect('/data-rights/request');
    }
}
