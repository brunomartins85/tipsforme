<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\Settlement;
use App\Services\AuditLogger;
use DateTimeImmutable;
use Throwable;

final class SettlementController
{
    public function index(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $month = $this->normalizedMonth((string) ($_GET['month'] ?? date('Y-m')));
        $model = new Settlement();

        View::render('settlements/index', [
            'month' => $month,
            'firstHalf' => $model->preview($restaurantId, Settlement::FIRST_HALF, $month),
            'monthEnd' => $model->preview($restaurantId, Settlement::MONTH_END, $month),
            'history' => $model->history($restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function preview(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $type = $this->normalizedType((string) ($_GET['type'] ?? ''));
        $month = $this->normalizedMonth((string) ($_GET['month'] ?? date('Y-m')));
        $summary = (new Settlement())->preview($restaurantId, $type, $month);

        if (!($summary['availability']['available'] ?? false)) {
            flash('error', trans('settlements.not_available', [
                'date' => format_date($summary['availability']['available_on']),
            ]));
            redirect('/settlements?month=' . urlencode($month));
        }

        View::render('settlements/preview', [
            'summary' => $summary,
            'paymentDate' => date('Y-m-d'),
            'notes' => '',
            'errors' => [],
            'error' => flash('error'),
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/settlements');
        }

        $user = auth_user();
        $type = $this->normalizedType((string) ($_POST['type'] ?? ''));
        $month = $this->normalizedMonth((string) ($_POST['reference_month'] ?? date('Y-m')));
        $paymentDate = trim((string) ($_POST['payment_date'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $errors = $this->validate($paymentDate, $notes);
        $model = new Settlement();
        $summary = $model->preview((int) $user['restaurant_id'], $type, $month);

        if (!($summary['availability']['available'] ?? false)) {
            flash('error', trans('settlements.not_available', [
                'date' => format_date($summary['availability']['available_on']),
            ]));
            redirect('/settlements?month=' . urlencode($month));
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('settlements/preview', [
                'summary' => $summary,
                'paymentDate' => $paymentDate,
                'notes' => $notes,
                'errors' => $errors,
                'error' => null,
            ]);
            return;
        }

        try {
            $settlementId = $model->create(
                (int) $user['restaurant_id'],
                (int) $user['id'],
                $type,
                $month,
                $paymentDate,
                $notes !== '' ? $notes : null
            );
            AuditLogger::record('settlement.create', 'settlement', $settlementId, null, [
                'type' => $type,
                'month' => $month,
                'total' => format_cents((int) $summary['totals']['total_cents']),
                'employees' => count($summary['payments']),
            ]);
            flash('success', trans('settlements.created'));
            redirect('/settlements/' . $settlementId);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', trans('settlements.create_failed'));
            redirect('/settlements/preview?type=' . urlencode($type) . '&month=' . urlencode($month));
        }
    }

    public function show(string $id): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $model = new Settlement();
        $settlement = $model->findById((int) $id, $restaurantId);

        if ($settlement === null) {
            flash('error', trans('settlements.not_found'));
            redirect('/settlements');
        }

        View::render('settlements/show', [
            'settlement' => $settlement,
            'payments' => $model->payments((int) $settlement['id'], $restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    private function normalizedType(string $type): string
    {
        return in_array($type, [Settlement::FIRST_HALF, Settlement::MONTH_END], true)
            ? $type
            : Settlement::FIRST_HALF;
    }

    private function normalizedMonth(string $month): string
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m', $month);

        return $date !== false && $date->format('Y-m') === $month
            ? $month
            : date('Y-m');
    }

    private function validate(string $paymentDate, string $notes): array
    {
        $errors = [];
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $paymentDate);

        if ($date === false || $date->format('Y-m-d') !== $paymentDate) {
            $errors['payment_date'] = trans('settlements.validation.payment_date');
        }

        if (text_length($notes) > 500) {
            $errors['notes'] = trans('settlements.validation.notes');
        }

        return $errors;
    }
}
