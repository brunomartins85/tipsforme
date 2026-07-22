<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\Restaurant;
use App\Models\TipEntry;
use App\Support\Money;
use App\Services\AuditLogger;
use Throwable;

final class TipEntryController
{
    public function index(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];

        View::render('entries/index', [
            'entries' => (new TipEntry())->allByRestaurant($restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function create(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $entryModel = new TipEntry();
        $availableShifts = $entryModel->availableShifts($restaurantId);
        $requestedShiftId = (int) ($_GET['shift_id'] ?? 0);
        $selectedShiftId = $this->resolveSelectedShiftId($availableShifts, $requestedShiftId);
        $restaurant = (new Restaurant())->findById($restaurantId);

        View::render('entries/form', [
            'entry' => [
                'shift_id' => $selectedShiftId,
                'cash_amount_input' => '',
                'card_gross_amount_input' => '',
                'notes' => '',
            ],
            'availableShifts' => $availableShifts,
            'feePercentage' => (string) ($restaurant['default_card_fee'] ?? '25.00'),
            'errors' => [],
            'mode' => 'create',
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/entries');
        }

        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $restaurant = (new Restaurant())->findById($restaurantId);
        $feePercentage = (string) ($restaurant['default_card_fee'] ?? '25.00');
        $entryModel = new TipEntry();
        $availableShifts = $entryModel->availableShifts($restaurantId);
        $data = $this->normalizedData($_POST, $feePercentage);
        $errors = $this->validate($data, $availableShifts);

        if ($errors !== []) {
            http_response_code(422);
            View::render('entries/form', [
                'entry' => $data,
                'availableShifts' => $availableShifts,
                'feePercentage' => $feePercentage,
                'errors' => $errors,
                'mode' => 'create',
            ]);
            return;
        }

        try {
            $entryId = $entryModel->create($restaurantId, (int) $user['id'], $data);
            AuditLogger::record('entry.create', 'tip_entry', $entryId, null, [
                'shift_id' => $data['shift_id'],
                'cash' => format_cents((int) $data['cash_cents']),
                'card_gross' => format_cents((int) $data['card_gross_cents']),
            ]);
            flash('success', trans('entries.created'));
            redirect('/entries/' . $entryId);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', trans('entries.create_failed'));
            redirect('/entries');
        }
    }

    public function show(string $id): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $entryModel = new TipEntry();
        $entry = $entryModel->findById((int) $id, $restaurantId);

        if ($entry === null) {
            flash('error', trans('entries.not_found'));
            redirect('/entries');
        }

        View::render('entries/show', [
            'entry' => $entry,
            'distributions' => $entryModel->distributions((int) $entry['id'], $restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function edit(string $id): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $entryModel = new TipEntry();
        $entry = $entryModel->findById((int) $id, $restaurantId);

        if ($entry === null) {
            flash('error', trans('entries.not_found'));
            redirect('/entries');
        }

        if ($entry['status'] !== 'open') {
            flash('error', trans('entries.settled_edit'));
            redirect('/entries/' . $entry['id']);
        }

        View::render('entries/form', [
            'entry' => [
                'id' => (int) $entry['id'],
                'shift_id' => (int) $entry['shift_id'],
                'shift_date' => $entry['shift_date'],
                'shift_type' => $entry['shift_type'],
                'employee_count' => (int) $entry['employee_count'],
                'employee_names' => $entry['employee_names'],
                'cash_amount_input' => money_input($entry['cash_amount']),
                'card_gross_amount_input' => money_input($entry['card_gross_amount']),
                'notes' => $entry['notes'] ?? '',
            ],
            'availableShifts' => [],
            'feePercentage' => (string) $entry['card_fee_percentage'],
            'errors' => [],
            'mode' => 'edit',
        ]);
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/entries');
        }

        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $entryId = (int) $id;
        $entryModel = new TipEntry();
        $existingEntry = $entryModel->findById($entryId, $restaurantId);

        if ($existingEntry === null) {
            flash('error', trans('entries.not_found'));
            redirect('/entries');
        }

        if ($existingEntry['status'] !== 'open') {
            flash('error', trans('entries.settled_edit'));
            redirect('/entries/' . $entryId);
        }

        $data = $this->normalizedData($_POST, (string) $existingEntry['card_fee_percentage']);
        $data['shift_id'] = (int) $existingEntry['shift_id'];
        $errors = $this->validateAmounts($data);

        if ($errors !== []) {
            http_response_code(422);
            View::render('entries/form', [
                'entry' => array_merge($data, [
                    'id' => $entryId,
                    'shift_date' => $existingEntry['shift_date'],
                    'shift_type' => $existingEntry['shift_type'],
                    'employee_count' => (int) $existingEntry['employee_count'],
                    'employee_names' => $existingEntry['employee_names'],
                ]),
                'availableShifts' => [],
                'feePercentage' => (string) $existingEntry['card_fee_percentage'],
                'errors' => $errors,
                'mode' => 'edit',
            ]);
            return;
        }

        try {
            $entryModel->update($entryId, $restaurantId, (int) $user['id'], $data);
            AuditLogger::record('entry.update', 'tip_entry', $entryId, null, [
                'cash' => format_cents((int) $data['cash_cents']),
                'card_gross' => format_cents((int) $data['card_gross_cents']),
            ]);
            flash('success', trans('entries.updated'));
            redirect('/entries/' . $entryId);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', trans('entries.update_failed'));
            redirect('/entries/' . $entryId);
        }
    }

    public function delete(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/entries');
        }

        $user = auth_user();

        try {
            $deleted = (new TipEntry())->delete(
                (int) $id,
                (int) $user['restaurant_id'],
                (int) $user['id']
            );

            if ($deleted) {
                AuditLogger::record('entry.delete', 'tip_entry', (int) $id);
            }

            flash(
                $deleted ? 'success' : 'error',
                $deleted ? trans('entries.deleted') : trans('entries.delete_failed')
            );
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', trans('entries.delete_failed'));
        }

        redirect('/entries');
    }

    private function normalizedData(array $input, string $feePercentage): array
    {
        $cashInput = trim((string) ($input['cash_amount'] ?? ''));
        $cardInput = trim((string) ($input['card_gross_amount'] ?? ''));

        return [
            'shift_id' => (int) ($input['shift_id'] ?? 0),
            'cash_amount_input' => $cashInput,
            'card_gross_amount_input' => $cardInput,
            'cash_cents' => Money::parseToCents($cashInput),
            'card_gross_cents' => Money::parseToCents($cardInput),
            'fee_percentage' => $feePercentage,
            'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
        ];
    }

    private function validate(array $data, array $availableShifts): array
    {
        $errors = $this->validateAmounts($data);
        $availableIds = array_map('intval', array_column($availableShifts, 'id'));

        if ($data['shift_id'] <= 0 || !in_array($data['shift_id'], $availableIds, true)) {
            $errors['shift_id'] = trans('entries.validation.shift');
        }

        return $errors;
    }

    private function validateAmounts(array $data): array
    {
        $errors = [];

        if ($data['cash_cents'] === null) {
            $errors['cash_amount'] = trans('entries.validation.cash');
        }

        if ($data['card_gross_cents'] === null) {
            $errors['card_gross_amount'] = trans('entries.validation.card');
        }

        if (
            $data['cash_cents'] !== null
            && $data['card_gross_cents'] !== null
            && ($data['cash_cents'] + $data['card_gross_cents']) <= 0
        ) {
            $errors['amounts'] = trans('entries.validation.total');
        }

        if ($data['notes'] !== null && text_length($data['notes']) > 500) {
            $errors['notes'] = trans('entries.validation.notes');
        }

        return $errors;
    }

    private function resolveSelectedShiftId(array $availableShifts, int $requestedShiftId): int
    {
        $availableIds = array_map('intval', array_column($availableShifts, 'id'));

        if ($requestedShiftId > 0 && in_array($requestedShiftId, $availableIds, true)) {
            return $requestedShiftId;
        }

        return $availableIds[0] ?? 0;
    }
}
