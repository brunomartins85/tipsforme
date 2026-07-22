<?php

declare(strict_types=1);

use App\Controllers\AuditLogController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DataRequestController;
use App\Controllers\LegalController;
use App\Controllers\OnboardingController;
use App\Controllers\RegistrationController;
use App\Controllers\SupportProjectController;
use App\Controllers\EmployeeAccessController;
use App\Controllers\EmployeeController;
use App\Controllers\EmployeePortalController;
use App\Controllers\PasswordController;
use App\Controllers\ReportController;
use App\Controllers\ShiftController;
use App\Controllers\SettlementController;
use App\Controllers\SettingsController;
use App\Controllers\TipEntryController;
use App\Middleware\AuthMiddleware;
use App\Middleware\EmployeeMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\ManagerMiddleware;


$router->get('/register', [RegistrationController::class, 'show'], [GuestMiddleware::class]);
$router->post('/register', [RegistrationController::class, 'store'], [GuestMiddleware::class]);
$router->get('/registration-pending', [RegistrationController::class, 'pending'], [GuestMiddleware::class]);
$router->get('/verify-email/resend', [RegistrationController::class, 'showResend'], [GuestMiddleware::class]);
$router->post('/verify-email/resend', [RegistrationController::class, 'resend'], [GuestMiddleware::class]);
$router->get('/verify-email', [RegistrationController::class, 'verify']);

$router->get('/legal/terms', [LegalController::class, 'terms']);
$router->get('/legal/privacy', [LegalController::class, 'privacy']);
$router->get('/legal/cookies', [LegalController::class, 'cookies']);
$router->get('/legal/data-rights', [LegalController::class, 'dataRights']);
$router->get('/support-project', [SupportProjectController::class, 'index']);

$router->get('/', [AuthController::class, 'home'], [AuthMiddleware::class]);
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

$router->get('/forgot-password', [PasswordController::class, 'showForgot'], [GuestMiddleware::class]);
$router->post('/forgot-password', [PasswordController::class, 'sendReset'], [GuestMiddleware::class]);
$router->get('/reset-password', [PasswordController::class, 'showReset'], [GuestMiddleware::class]);
$router->post('/reset-password', [PasswordController::class, 'reset'], [GuestMiddleware::class]);


$router->get('/onboarding', [OnboardingController::class, 'index'], [AuthMiddleware::class]);
$router->post('/onboarding', [OnboardingController::class, 'complete'], [AuthMiddleware::class]);
$router->get('/data-rights/request', [DataRequestController::class, 'create'], [AuthMiddleware::class]);
$router->post('/data-rights/request', [DataRequestController::class, 'store'], [AuthMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [ManagerMiddleware::class]);

$router->get('/audit', [AuditLogController::class, 'index'], [ManagerMiddleware::class]);

$router->get('/reports', [ReportController::class, 'index'], [ManagerMiddleware::class]);
$router->get('/reports/export/csv', [ReportController::class, 'csv'], [ManagerMiddleware::class]);
$router->get('/reports/export/pdf', [ReportController::class, 'pdf'], [ManagerMiddleware::class]);

$router->get('/settings', [SettingsController::class, 'index'], [ManagerMiddleware::class]);
$router->post('/settings/restaurant', [SettingsController::class, 'updateRestaurant'], [ManagerMiddleware::class]);
$router->post('/settings/company', [SettingsController::class, 'updateCompany'], [ManagerMiddleware::class]);
$router->post('/settings/profile', [SettingsController::class, 'updateProfile'], [ManagerMiddleware::class]);
$router->post('/settings/password', [SettingsController::class, 'updatePassword'], [ManagerMiddleware::class]);

$router->get('/employees', [EmployeeController::class, 'index'], [ManagerMiddleware::class]);
$router->get('/employees/create', [EmployeeController::class, 'create'], [ManagerMiddleware::class]);
$router->post('/employees', [EmployeeController::class, 'store'], [ManagerMiddleware::class]);
$router->get('/employees/{id}/edit', [EmployeeController::class, 'edit'], [ManagerMiddleware::class]);
$router->post('/employees/{id}/update', [EmployeeController::class, 'update'], [ManagerMiddleware::class]);
$router->post('/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'], [ManagerMiddleware::class]);
$router->post('/employees/{id}/send-access', [EmployeeAccessController::class, 'send'], [ManagerMiddleware::class]);

$router->get('/shifts', [ShiftController::class, 'index'], [ManagerMiddleware::class]);
$router->get('/shifts/create', [ShiftController::class, 'create'], [ManagerMiddleware::class]);
$router->post('/shifts', [ShiftController::class, 'store'], [ManagerMiddleware::class]);
$router->get('/shifts/{id}/edit', [ShiftController::class, 'edit'], [ManagerMiddleware::class]);
$router->post('/shifts/{id}/update', [ShiftController::class, 'update'], [ManagerMiddleware::class]);
$router->post('/shifts/{id}/delete', [ShiftController::class, 'delete'], [ManagerMiddleware::class]);

$router->get('/entries', [TipEntryController::class, 'index'], [ManagerMiddleware::class]);
$router->get('/entries/create', [TipEntryController::class, 'create'], [ManagerMiddleware::class]);
$router->post('/entries', [TipEntryController::class, 'store'], [ManagerMiddleware::class]);
$router->get('/entries/{id}', [TipEntryController::class, 'show'], [ManagerMiddleware::class]);
$router->get('/entries/{id}/edit', [TipEntryController::class, 'edit'], [ManagerMiddleware::class]);
$router->post('/entries/{id}/update', [TipEntryController::class, 'update'], [ManagerMiddleware::class]);
$router->post('/entries/{id}/delete', [TipEntryController::class, 'delete'], [ManagerMiddleware::class]);

$router->get('/settlements', [SettlementController::class, 'index'], [ManagerMiddleware::class]);
$router->get('/settlements/preview', [SettlementController::class, 'preview'], [ManagerMiddleware::class]);
$router->post('/settlements', [SettlementController::class, 'store'], [ManagerMiddleware::class]);
$router->get('/settlements/{id}', [SettlementController::class, 'show'], [ManagerMiddleware::class]);

$router->get('/my/dashboard', [EmployeePortalController::class, 'dashboard'], [EmployeeMiddleware::class]);
$router->get('/my/statement', [EmployeePortalController::class, 'statement'], [EmployeeMiddleware::class]);
$router->get('/my/payments', [EmployeePortalController::class, 'payments'], [EmployeeMiddleware::class]);
