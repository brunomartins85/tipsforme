<?php

declare(strict_types=1);

namespace App\Services;

final class MonthlyReportPdf
{
    private SimplePdf $pdf;
    private float $y = 0;
    private string $restaurantName = '';
    private string $periodLabel = '';
    private int $pageNumber = 0;

    public function generate(string $restaurantName, string $periodLabel, array $report, ?array $employee): string
    {
        $this->pdf = new SimplePdf();
        $this->restaurantName = $restaurantName;
        $this->periodLabel = $periodLabel;
        $this->newPage();

        $filter = $employee !== null
            ? trans('reports.pdf.employee', ['name' => $employee['name']])
            : trans('reports.pdf.all_employees');

        $this->pdf->text(40, $this->y, trans('reports.pdf.title'), 18, 'F2');
        $this->y -= 22;
        $this->pdf->text(40, $this->y, $restaurantName . ' · ' . $periodLabel, 10, 'F1');
        $this->y -= 16;
        $this->pdf->text(40, $this->y, $filter, 9, 'F1');
        $this->y -= 24;

        $totals = $report['totals'];
        $summaryLines = [
            trans('reports.total_net') . ': ' . $this->currency($totals['total_amount'] ?? 0),
            trans('reports.cash') . ': ' . $this->currency($totals['cash_total'] ?? 0),
            trans('reports.card_gross') . ': ' . $this->currency($totals['card_gross_total'] ?? 0),
            trans('reports.card_fee') . ': ' . $this->currency($totals['card_fee_total'] ?? 0),
            trans('reports.pending') . ': ' . $this->currency($totals['pending_total'] ?? 0),
            trans('reports.paid') . ': ' . $this->currency($totals['paid_total'] ?? 0),
        ];

        $this->pdf->rectangle(40, $this->y - 48, 515, 58, 0.96);
        foreach (array_chunk($summaryLines, 3) as $rowIndex => $row) {
            foreach ($row as $columnIndex => $line) {
                $this->pdf->text(52 + ($columnIndex * 168), $this->y - 12 - ($rowIndex * 20), $line, 8.5, 'F2');
            }
        }
        $this->y -= 72;

        $this->sectionTitle(trans('reports.employee_summary'));
        $this->employeeTable($report['employee_summary']);
        $this->y -= 14;
        $this->sectionTitle(trans('reports.details'));
        $this->detailTable($report['details']);

        return $this->pdf->output();
    }

    private function employeeTable(array $rows): void
    {
        $headers = [
            trans('reports.employee'),
            trans('reports.shifts'),
            trans('reports.cash'),
            trans('reports.card_net'),
            trans('reports.total_net'),
            trans('reports.pending'),
        ];
        $widths = [150, 45, 75, 75, 80, 80];
        $this->tableHeader($headers, $widths);

        if ($rows === []) {
            $this->pdf->text(44, $this->y, trans('reports.empty'), 9, 'F1');
            $this->y -= 18;
            return;
        }

        foreach ($rows as $row) {
            $this->ensureSpace(20, fn () => $this->tableHeader($headers, $widths));
            $this->tableRow([
                (string) $row['name'],
                (string) $row['shift_count'],
                $this->currency($row['cash_total']),
                $this->currency($row['card_net_total']),
                $this->currency($row['total_amount']),
                $this->currency($row['pending_total']),
            ], $widths);
        }
    }

    private function detailTable(array $rows): void
    {
        $headers = [
            trans('reports.date'),
            trans('reports.shift'),
            trans('reports.employee'),
            trans('reports.cash'),
            trans('reports.card_net'),
            trans('reports.total_net'),
            trans('reports.status'),
        ];
        $widths = [58, 54, 132, 70, 70, 72, 59];
        $this->tableHeader($headers, $widths);

        if ($rows === []) {
            $this->pdf->text(44, $this->y, trans('reports.empty'), 9, 'F1');
            $this->y -= 18;
            return;
        }

        foreach ($rows as $row) {
            $this->ensureSpace(20, fn () => $this->tableHeader($headers, $widths));
            $this->tableRow([
                format_date((string) $row['shift_date']),
                trans('shifts.type.' . $row['shift_type']),
                (string) $row['name'],
                $this->currency($row['cash_amount']),
                $this->currency($row['card_net_amount']),
                $this->currency($row['total_amount']),
                trans('reports.status.' . $row['payment_status']),
            ], $widths);
        }
    }

    private function sectionTitle(string $title): void
    {
        $this->ensureSpace(28);
        $this->pdf->text(40, $this->y, $title, 12, 'F2');
        $this->y -= 17;
        $this->pdf->line(40, $this->y, 555, $this->y, 0.6);
        $this->y -= 12;
    }

    private function tableHeader(array $cells, array $widths): void
    {
        $this->ensureSpace(22);
        $this->pdf->rectangle(40, $this->y - 5, 515, 17, 0.91);
        $this->tableRow($cells, $widths, true);
    }

    private function tableRow(array $cells, array $widths, bool $bold = false): void
    {
        $x = 44;
        $font = $bold ? 'F2' : 'F3';
        $size = $bold ? 7.5 : 7.2;

        foreach ($cells as $index => $cell) {
            $width = $widths[$index] ?? 60;
            $this->pdf->text($x, $this->y, $this->truncate((string) $cell, $width, $size), $size, $font);
            $x += $width;
        }

        $this->y -= 16;
        $this->pdf->line(40, $this->y + 5, 555, $this->y + 5, 0.2);
    }

    private function newPage(): void
    {
        $this->pdf->addPage();
        $this->pageNumber++;
        $this->y = SimplePdf::pageHeight() - 44;
        $this->pdf->text(40, $this->y, 'tipsforme', 11, 'F2');
        $this->pdf->text(438, $this->y, $this->periodLabel, 8, 'F1');
        $this->pdf->text(532, 24, (string) $this->pageNumber, 8, 'F1');
        $this->y -= 28;
    }

    private function ensureSpace(float $height, ?callable $afterNewPage = null): void
    {
        if ($this->y - $height >= 44) {
            return;
        }

        $this->newPage();

        if ($afterNewPage !== null) {
            $afterNewPage();
        }
    }

    private function truncate(string $value, float $width, float $size): string
    {
        $maxCharacters = max(3, (int) floor($width / ($size * 0.58)));

        if (text_length($value) <= $maxCharacters) {
            return $value;
        }

        $slice = function_exists('mb_substr')
            ? mb_substr($value, 0, $maxCharacters - 1, 'UTF-8')
            : substr($value, 0, $maxCharacters - 1);

        return $slice . '…';
    }

    private function currency(string|float|int|null $value): string
    {
        return 'EUR ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
