<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Employee;
use App\Models\Report;
use App\Models\Restaurant;
use App\Services\AuditLogger;
use App\Services\MonthlyReportPdf;
use DateTimeImmutable;
use Throwable;

final class ReportController
{
    public function index(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        [$month, $employeeId] = $this->filters($restaurantId);
        $reportModel = new Report();
        $employees = (new Employee())->allByRestaurant($restaurantId);
        $selectedEmployee = $this->selectedEmployee($employees, $employeeId);

        View::render('reports/index', [
            'month' => $month,
            'employeeId' => $employeeId,
            'selectedEmployee' => $selectedEmployee,
            'employees' => $employees,
            'report' => $reportModel->monthly($restaurantId, $month, $employeeId),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function csv(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        [$month, $employeeId] = $this->filters($restaurantId);
        $report = (new Report())->monthly($restaurantId, $month, $employeeId);
        $filename = 'tipsforme-report-' . $month . ($employeeId !== null ? '-employee-' . $employeeId : '') . '.csv';

        AuditLogger::record('report.export.csv', 'report', null, null, [
            'month' => $month,
            'employee_id' => $employeeId,
            'rows' => count($report['details']),
        ]);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'wb');

        if ($output === false) {
            return;
        }

        fputcsv($output, [
            trans('reports.csv.date'),
            trans('reports.csv.shift'),
            trans('reports.csv.employee'),
            trans('reports.csv.position'),
            trans('reports.csv.cash'),
            trans('reports.csv.card_gross'),
            trans('reports.csv.card_fee'),
            trans('reports.csv.card_net'),
            trans('reports.csv.total'),
            trans('reports.csv.status'),
        ], ';', '"', '');

        foreach ($report['details'] as $row) {
            fputcsv($output, [
                (string) $row['shift_date'],
                trans('shifts.type.' . $row['shift_type']),
                $this->safeCsvCell((string) $row['name']),
                $this->safeCsvCell((string) $row['position']),
                number_format((float) $row['cash_amount'], 2, ',', ''),
                number_format((float) $row['card_gross_amount'], 2, ',', ''),
                number_format((float) $row['card_fee_amount'], 2, ',', ''),
                number_format((float) $row['card_net_amount'], 2, ',', ''),
                number_format((float) $row['total_amount'], 2, ',', ''),
                trans('reports.status.' . $row['payment_status']),
            ], ';', '"', '');
        }

        fclose($output);
    }

    public function pdf(): void
    {
        $user = auth_user();
        $restaurantId = (int) $user['restaurant_id'];
        [$month, $employeeId] = $this->filters($restaurantId);
        $reportModel = new Report();
        $report = $reportModel->monthly($restaurantId, $month, $employeeId);
        $restaurant = (new Restaurant())->findById($restaurantId);
        $employees = (new Employee())->allByRestaurant($restaurantId);
        $selectedEmployee = $this->selectedEmployee($employees, $employeeId);
        $filename = 'tipsforme-report-' . $month . ($employeeId !== null ? '-employee-' . $employeeId : '') . '.pdf';

        try {
            $content = (new MonthlyReportPdf())->generate(
                (string) ($restaurant['name'] ?? $user['restaurant_name'] ?? 'TipsForMe'),
                format_month($month),
                $report,
                $selectedEmployee
            );
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            flash('error', trans('reports.export_failed'));
            redirect('/reports?month=' . urlencode($month));
        }

        AuditLogger::record('report.export.pdf', 'report', null, null, [
            'month' => $month,
            'employee_id' => $employeeId,
            'rows' => count($report['details']),
        ]);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('X-Content-Type-Options: nosniff');
        echo $content;
    }

    private function safeCsvCell(string $value): string
    {
        $trimmed = ltrim($value);

        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    private function filters(int $restaurantId): array
    {
        $monthInput = trim((string) ($_GET['month'] ?? date('Y-m')));
        $date = DateTimeImmutable::createFromFormat('!Y-m', $monthInput);
        $month = $date !== false && $date->format('Y-m') === $monthInput
            ? $monthInput
            : date('Y-m');
        $employeeInput = $_GET['employee_id'] ?? null;
        $employeeId = filter_var($employeeInput, FILTER_VALIDATE_INT);
        $employeeId = is_int($employeeId) && $employeeId > 0 ? $employeeId : null;

        if ($employeeId !== null && !(new Report())->employeeBelongsToRestaurant($employeeId, $restaurantId)) {
            $employeeId = null;
        }

        return [$month, $employeeId];
    }

    private function selectedEmployee(array $employees, ?int $employeeId): ?array
    {
        if ($employeeId === null) {
            return null;
        }

        foreach ($employees as $employee) {
            if ((int) $employee['id'] === $employeeId) {
                return $employee;
            }
        }

        return null;
    }
}
