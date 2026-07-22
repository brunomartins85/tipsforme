<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Locale;
use App\Core\View;

final class LegalController
{
    public function terms(): void
    {
        $this->render('terms');
    }

    public function privacy(): void
    {
        $this->render('privacy');
    }

    public function cookies(): void
    {
        $this->render('cookies');
    }

    public function dataRights(): void
    {
        $this->render('data-rights');
    }

    private function render(string $document): void
    {
        $content = $this->documents()[Locale::current()][$document] ?? [];
        $layout = auth_user() === null
            ? 'auth'
            : ((auth_user()['role'] ?? '') === 'employee' ? 'employee' : 'app');

        View::render('legal/document', [
            'document' => $document,
            'documentContent' => $content,
            'contactEmail' => (string) env('LEGAL_CONTACT_EMAIL', 'tips@tipsforme.club'),
            'updatedAt' => (string) env('LEGAL_DOCUMENT_DATE', '21/07/2026'),
        ], $layout);
    }

    private function documents(): array
    {
        return require dirname(__DIR__, 2) . '/config/legal.php';
    }
}
