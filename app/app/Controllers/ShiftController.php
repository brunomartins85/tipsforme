<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\AuditLogger;
use DateTimeImmutable;

final class ShiftController
{
    public function index(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];

        View::render('shifts/index', [
            'shifts' => (new Shift())->allByRestaurant($restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function create(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];

        View::render('shifts/form', [
            'shift' => [
                'shift_date' => date('Y-m-d'),
                'shift_type' => 'lunch',
                'notes' => '',
                'status' => 'open',
            ],
            'employees' => (new Employee())->activeByRestaurant($restaurantId),
            'selectedEmployeeIds' => [],
            'errors' => [],
            'mode' => 'create',
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/shifts');
        }

        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        $data = $this->normalizedData($_POST);
        $selectedEmployeeIds = $this->normalizedEmployeeIds($_POST['employee_ids'] ?? []);
        $employeeModel = new Employee();
        $employees = $employeeModel->activeByRestaurant($restaurantId);
        $allowedIds = array_map('intval', array_column($employees, 'id'));
        $selectedEmployeeIds = array_values(array_intersect($selectedEmployeeIds, $allowedIds));
        $errors = $this->validate($data, $selectedEmployeeIds);
        $shiftModel = new Shift();

        if ($shiftModel->existsForDateAndType($restaurantId, $data['shift_date'], $data['shift_type'])) {
            $errors['shift_type'] = trans('shifts.validation.duplicate');
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('shifts/form', [
                'shift' => $data,
                'employees' => $employees,
                'selectedEmployeeIds' => $selectedEmployeeIds,
                'errors' => $errors,
                'mode' => 'create',
            ]);
            return;
        }

        $shiftId = $shiftModel->create($restaurantId, (int) $user['id'], $data, $selectedEmployeeIds);
        AuditLogger::record('shift.create', 'shift', $shiftId, null, [
            'date' => $data['shift_date'],
            'type' => $data['shift_type'],
            'employees' => count($selectedEmployeeIds),
        ]);
        flash('success', trans('shifts.created'));
        redirect('/shifts');
    }

    public function edit(string $id): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $shiftModel = new Shift();
        $shift = $shiftModel->findById((int) $id, $restaurantId);

        if ($shift === null) {
            flash('error', trans('shifts.not_found'));
            redirect('/shifts');
        }

        if ($shift['status'] !== 'open') {
            flash('error', trans('shifts.closed_edit'));
            redirect('/shifts');
        }

        $selectedEmployeeIds = $shiftModel->employeeIds((int) $shift['id'], $restaurantId);

        View::render('shifts/form', [
            'shift' => $shift,
            'employees' => (new Employee())->allByRestaurant($restaurantId),
            'selectedEmployeeIds' => $selectedEmployeeIds,
            'errors' => [],
            'mode' => 'edit',
        ]);
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/shifts');
        }

        $user = auth_user();
        $shiftId = (int) $id;
        $restaurantId = (int) $user['restaurant_id'];
        $shiftModel = new Shift();
        $existingShift = $shiftModel->findById($shiftId, $restaurantId);

        if ($existingShift === null) {
            flash('error', trans('shifts.not_found'));
            redirect('/shifts');
        }

        if ($existingShift['status'] !== 'open') {
            flash('error', trans('shifts.closed_edit'));
            redirect('/shifts');
        }

        $data = $this->normalizedData($_POST);
        $data['id'] = $shiftId;
        $data['status'] = $existingShift['status'];
        $selectedEmployeeIds = $this->normalizedEmployeeIds($_POST['employee_ids'] ?? []);
        $currentEmployeeIds = $shiftModel->employeeIds($shiftId, $restaurantId);
        $employees = (new Employee())->allByRestaurant($restaurantId);
        $allowedIds = [];

        foreach ($employees as $employee) {
            $employeeId = (int) $employee['id'];

            if ($employee['status'] === 'active' || in_array($employeeId, $currentEmployeeIds, true)) {
                $allowedIds[] = $employeeId;
            }
        }

        $selectedEmployeeIds = array_values(array_intersect($selectedEmployeeIds, $allowedIds));
        $errors = $this->validate($data, $selectedEmployeeIds);

        if ($shiftModel->existsForDateAndType(
            $restaurantId,
            $data['shift_date'],
            $data['shift_type'],
            $shiftId
        )) {
            $errors['shift_type'] = trans('shifts.validation.duplicate');
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('shifts/form', [
                'shift' => $data,
                'employees' => $employees,
                'selectedEmployeeIds' => $selectedEmployeeIds,
                'errors' => $errors,
                'mode' => 'edit',
            ]);
            return;
        }

        $shiftModel->update($shiftId, $restaurantId, (int) $user['id'], $data, $selectedEmployeeIds);
        AuditLogger::record('shift.update', 'shift', $shiftId, null, [
            'date' => $data['shift_date'],
            'type' => $data['shift_type'],
            'employees' => count($selectedEmployeeIds),
        ]);
        flash('success', trans('shifts.updated'));
        redirect('/shifts');
    }

    public function delete(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/shifts');
        }

        $restaurantId = (int) auth_user()['restaurant_id'];
        $shiftId = (int) $id;
        $model = new Shift();
        $existing = $model->findById($shiftId, $restaurantId);
        $deleted = $model->delete($shiftId, $restaurantId);

        if ($deleted) {
            AuditLogger::record('shift.delete', 'shift', $shiftId, null, [
                'date' => $existing['shift_date'] ?? '',
                'type' => $existing['shift_type'] ?? '',
            ]);
        }

        flash(
            $deleted ? 'success' : 'error',
            $deleted ? trans('shifts.deleted') : trans('shifts.delete_failed')
        );
        redirect('/shifts');
    }

    private function normalizedData(array $input): array
    {
        return [
            'shift_date' => trim((string) ($input['shift_date'] ?? '')),
            'shift_type' => (string) ($input['shift_type'] ?? 'lunch'),
            'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
        ];
    }

    private function normalizedEmployeeIds(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $ids = array_map('intval', $input);
        $ids = array_filter($ids, static fn (int $id): bool => $id > 0);

        return array_values(array_unique($ids));
    }

    private function validate(array $data, array $selectedEmployeeIds): array
    {
        $errors = [];
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $data['shift_date']);

        if ($date === false || $date->format('Y-m-d') !== $data['shift_date']) {
            $errors['shift_date'] = trans('shifts.validation.date');
        }

        if (!in_array($data['shift_type'], ['lunch', 'dinner'], true)) {
            $errors['shift_type'] = trans('shifts.validation.type');
        }

        if ($data['notes'] !== null && text_length($data['notes']) > 500) {
            $errors['notes'] = trans('shifts.validation.notes');
        }

        if ($selectedEmployeeIds === []) {
            $errors['employee_ids'] = trans('shifts.validation.employees');
        }

        return $errors;
    }
}
