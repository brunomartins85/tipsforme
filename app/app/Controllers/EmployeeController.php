<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Models\Employee;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\AuditLogger;

final class EmployeeController
{
    public function index(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];

        View::render('employees/index', [
            'employees' => (new Employee())->allByRestaurant($restaurantId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function create(): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $restaurant = (new Restaurant())->findById($restaurantId);

        View::render('employees/form', [
            'employee' => [
                'name' => '',
                'email' => '',
                'position' => '',
                'language' => $restaurant['default_language'] ?? 'pt',
                'status' => 'active',
            ],
            'errors' => [],
            'mode' => 'create',
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/employees');
        }

        $restaurantId = (int) auth_user()['restaurant_id'];
        $data = $this->normalizedData($_POST);
        $errors = $this->validate($data);
        $model = new Employee();

        if ($data['email'] !== null && $model->emailExists($data['email'], $restaurantId)) {
            $errors['email'] = trans('employees.validation.email_unique');
        }

        if ($data['email'] !== null && (new User())->emailExists($data['email'])) {
            $errors['email'] = trans('employees.validation.user_email_unique');
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('employees/form', [
                'employee' => $data,
                'errors' => $errors,
                'mode' => 'create',
            ]);
            return;
        }

        $employeeId = $model->create($restaurantId, $data);
        AuditLogger::record('employee.create', 'employee', $employeeId, null, [
            'name' => $data['name'],
            'position' => $data['position'],
        ]);
        flash('success', trans('employees.created'));
        redirect('/employees');
    }

    public function edit(string $id): void
    {
        $restaurantId = (int) auth_user()['restaurant_id'];
        $employee = (new Employee())->findById((int) $id, $restaurantId);

        if ($employee === null) {
            flash('error', trans('employees.not_found'));
            redirect('/employees');
        }

        View::render('employees/form', [
            'employee' => $employee,
            'errors' => [],
            'mode' => 'edit',
        ]);
    }

    public function update(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/employees');
        }

        $employeeId = (int) $id;
        $restaurantId = (int) auth_user()['restaurant_id'];
        $model = new Employee();
        $existingEmployee = $model->findById($employeeId, $restaurantId);

        if ($existingEmployee === null) {
            flash('error', trans('employees.not_found'));
            redirect('/employees');
        }

        $data = $this->normalizedData($_POST);
        $data['id'] = $employeeId;
        $data['status'] = $existingEmployee['status'];
        $errors = $this->validate($data);

        if ($data['email'] !== null && $model->emailExists($data['email'], $restaurantId, $employeeId)) {
            $errors['email'] = trans('employees.validation.email_unique');
        }

        if ($data['email'] !== null && (new User())->emailExists(
            $data['email'],
            !empty($existingEmployee['user_id']) ? (int) $existingEmployee['user_id'] : null
        )) {
            $errors['email'] = trans('employees.validation.user_email_unique');
        }

        if ($errors !== []) {
            http_response_code(422);
            View::render('employees/form', [
                'employee' => $data,
                'errors' => $errors,
                'mode' => 'edit',
            ]);
            return;
        }

        $model->update($employeeId, $restaurantId, $data);
        AuditLogger::record('employee.update', 'employee', $employeeId, null, [
            'name' => $data['name'],
            'position' => $data['position'],
        ]);
        flash('success', trans('employees.updated'));
        redirect('/employees');
    }

    public function toggleStatus(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/employees');
        }

        $restaurantId = (int) auth_user()['restaurant_id'];
        $model = new Employee();
        $employeeId = (int) $id;
        $updated = $model->toggleStatus($employeeId, $restaurantId);

        if ($updated) {
            $employee = $model->findById($employeeId, $restaurantId);
            AuditLogger::record('employee.status', 'employee', $employeeId, null, [
                'status' => $employee['status'] ?? 'unknown',
                'name' => $employee['name'] ?? '',
            ]);
        }

        flash(
            $updated ? 'success' : 'error',
            $updated ? trans('employees.status_updated') : trans('employees.not_found')
        );
        redirect('/employees');
    }

    private function normalizedData(array $input): array
    {
        $email = text_lower(trim((string) ($input['email'] ?? '')));

        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => $email !== '' ? $email : null,
            'position' => trim((string) ($input['position'] ?? '')),
            'language' => (string) ($input['language'] ?? 'pt'),
            'status' => 'active',
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (text_length($data['name']) < 2 || text_length($data['name']) > 120) {
            $errors['name'] = trans('employees.validation.name');
        }

        if ($data['email'] !== null && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = trans('employees.validation.email');
        }

        if (text_length($data['position']) < 2 || text_length($data['position']) > 80) {
            $errors['position'] = trans('employees.validation.position');
        }

        if (!in_array($data['language'], ['pt', 'en'], true)) {
            $errors['language'] = trans('employees.validation.language');
        }

        return $errors;
    }
}
