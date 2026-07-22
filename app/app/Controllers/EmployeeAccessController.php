<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PasswordResetService;
use App\Services\SmtpMailer;
use RuntimeException;
use Throwable;

final class EmployeeAccessController
{
    public function send(string $id): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', trans('auth.invalid_request'));
            redirect('/employees');
        }

        $restaurantId = (int) auth_user()['restaurant_id'];
        $employeeId = (int) $id;
        $employeeModel = new Employee();
        $employee = $employeeModel->findById($employeeId, $restaurantId);

        if ($employee === null) {
            flash('error', trans('employees.not_found'));
            redirect('/employees');
        }

        if ($employee['status'] !== 'active') {
            flash('error', trans('access.employee_inactive'));
            redirect('/employees');
        }

        if (empty($employee['email']) || !filter_var($employee['email'], FILTER_VALIDATE_EMAIL)) {
            flash('error', trans('access.email_required'));
            redirect('/employees/' . $employeeId . '/edit');
        }

        try {
            $userId = !empty($employee['user_id'])
                ? (int) $employee['user_id']
                : $this->createAndLinkUser($employee, $restaurantId);

            $token = (new PasswordResetService())->issue($restaurantId, $userId, 'activation');
            $link = absolute_url('/reset-password?token=' . urlencode($token));
            (new SmtpMailer())->send(
                $employee['email'],
                $employee['name'],
                $employee['language'] === 'en' ? 'Your TipsForMe access' : 'Seu acesso ao TipsForMe',
                $this->activationEmail($employee, $link)
            );

            AuditLogger::record('employee.access', 'employee', $employeeId, null, [
                'name' => $employee['name'],
            ]);
            flash('success', trans('access.sent'));
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', trans('access.send_failed'));
        }

        redirect('/employees');
    }

    private function createAndLinkUser(array $employee, int $restaurantId): int
    {
        $userModel = new User();

        if ($userModel->emailExists((string) $employee['email'])) {
            throw new RuntimeException('The email already belongs to another application user.');
        }

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            // A senha aleatória nunca é entregue; o colaborador define uma senha pelo link.
            $userId = $userModel->createEmployeeUser(
                $restaurantId,
                $employee,
                password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT)
            );

            if (!(new Employee())->linkUser((int) $employee['id'], $restaurantId, $userId)) {
                throw new RuntimeException('Unable to link employee access.');
            }

            $connection->commit();
            return $userId;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private function activationEmail(array $employee, string $link): string
    {
        $english = $employee['language'] === 'en';
        $title = $english ? 'Your employee portal is ready' : 'Seu painel de colaborador está pronto';
        $message = $english
            ? 'Use the button below to choose your password and access your balance, statement and payment history. The link expires in 60 minutes.'
            : 'Use o botão abaixo para escolher sua senha e acessar seu saldo, extrato e histórico de pagamentos. O link expira em 60 minutos.';
        $button = $english ? 'Activate my access' : 'Ativar meu acesso';
        $ignore = $english
            ? 'This invitation was sent by your restaurant manager.'
            : 'Este convite foi enviado pelo gestor do seu restaurante.';

        return email_template($title, $employee['name'], $message, $button, $link, $ignore);
    }
}
