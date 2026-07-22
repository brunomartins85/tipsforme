<?php

declare(strict_types=1);

namespace App\Services;

final class SimplePdf
{
    private const PAGE_WIDTH = 595;
    private const PAGE_HEIGHT = 842;

    private array $pages = [];
    private array $currentCommands = [];

    public function addPage(): void
    {
        if ($this->currentCommands !== []) {
            $this->pages[] = implode("\n", $this->currentCommands);
        }

        $this->currentCommands = [];
    }

    public function text(float $x, float $y, string $text, float $size = 10, string $font = 'F1'): void
    {
        $encoded = $this->encode($text);
        $this->currentCommands[] = sprintf(
            'BT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET',
            $font,
            $size,
            $x,
            $y,
            $encoded
        );
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.5): void
    {
        $this->currentCommands[] = sprintf(
            '%.2F w %.2F %.2F m %.2F %.2F l S',
            $width,
            $x1,
            $y1,
            $x2,
            $y2
        );
    }

    public function rectangle(float $x, float $y, float $width, float $height, float $gray = 0.95): void
    {
        $this->currentCommands[] = sprintf(
            '%.3F g %.2F %.2F %.2F %.2F re f 0 g',
            max(0, min(1, $gray)),
            $x,
            $y,
            $width,
            $height
        );
    }

    public function output(): string
    {
        if ($this->currentCommands !== [] || $this->pages === []) {
            $this->pages[] = implode("\n", $this->currentCommands);
            $this->currentCommands = [];
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>';

        $pageReferences = [];
        $nextObject = 6;

        foreach ($this->pages as $commands) {
            $pageObject = $nextObject++;
            $contentObject = $nextObject++;
            $pageReferences[] = $pageObject . ' 0 R';
            $stream = $commands . "\n";

            $objects[$contentObject] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}endstream";
            $objects[$pageObject] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $contentObject
            );
        }

        $objects[2] = sprintf(
            '<< /Type /Pages /Kids [%s] /Count %d >>',
            implode(' ', $pageReferences),
            count($pageReferences)
        );
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxObject = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($number = 1; $number <= $maxObject; $number++) {
            $offset = $offsets[$number] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    public static function pageHeight(): int
    {
        return self::PAGE_HEIGHT;
    }

    private function encode(string $text): string
    {
        $converted = function_exists('iconv')
            ? iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text)
            : $text;
        $converted = is_string($converted) ? $converted : $text;

        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', ' ', ' '],
            $converted
        );
    }
}
