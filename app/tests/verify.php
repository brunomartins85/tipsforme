<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Models\Settlement;
use App\Support\Money;

$failures = [];
$checks = 0;

$assert = static function (bool $condition, string $message) use (&$failures, &$checks): void {
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
};

// Valores monetários e arredondamentos.
$assert(Money::parseToCents('100,00') === 10000, 'Falha ao converter 100,00.');
$assert(Money::parseToCents('1.234,56') === 123456, 'Falha ao converter 1.234,56.');
$assert(Money::feeInCents(5000, 25) === 1250, 'Falha ao calcular taxa de 25%.');
$split = Money::splitCents(10000, [3, 1, 2]);
$assert(array_sum($split) === 10000, 'A divisão perdeu centavos.');
$assert(max($split) - min($split) <= 1, 'A divisão não foi equilibrada.');

// Períodos de fechamento, inclusive fevereiro bissexto.
$settlement = new Settlement();
$firstHalf = $settlement->periods(Settlement::FIRST_HALF, '2028-02', 15);
$monthEnd = $settlement->periods(Settlement::MONTH_END, '2028-02', 15);
$assert($firstHalf['cash_end'] === '2028-02-15', 'Primeira quinzena incorreta.');
$assert($monthEnd['cash_start'] === '2028-02-16', 'Início da segunda quinzena incorreto.');
$assert($monthEnd['cash_end'] === '2028-02-29', 'Fevereiro bissexto incorreto.');
$assert($monthEnd['card_start'] === '2028-02-01' && $monthEnd['card_end'] === '2028-02-29', 'Período de multibanco incorreto.');

// Fechamentos do mês atual só ficam disponíveis na data correta.
$beforeFirstHalf = $settlement->availability(
    Settlement::FIRST_HALF,
    '2026-07',
    15,
    new DateTimeImmutable('2026-07-14')
);
$onFirstHalf = $settlement->availability(
    Settlement::FIRST_HALF,
    '2026-07',
    15,
    new DateTimeImmutable('2026-07-15')
);
$beforeMonthEnd = $settlement->availability(
    Settlement::MONTH_END,
    '2026-07',
    15,
    new DateTimeImmutable('2026-07-30')
);
$onMonthEnd = $settlement->availability(
    Settlement::MONTH_END,
    '2026-07',
    15,
    new DateTimeImmutable('2026-07-31')
);
$pastMonth = $settlement->availability(
    Settlement::MONTH_END,
    '2026-06',
    15,
    new DateTimeImmutable('2026-07-01')
);
$assert($beforeFirstHalf['available'] === false, 'Primeira quinzena foi liberada antes da data.');
$assert($onFirstHalf['available'] === true, 'Primeira quinzena não foi liberada na data correta.');
$assert($beforeMonthEnd['available'] === false, 'Fechamento mensal foi liberado antes do último dia.');
$assert($onMonthEnd['available'] === true, 'Fechamento mensal não foi liberado no último dia.');
$assert($pastMonth['available'] === true, 'Mês anterior deveria continuar disponível para regularização.');


// Cadastro empresarial e documentos públicos.
$assert(slugify('Villa Meco & Café') === 'villa-meco-cafe', 'Falha ao gerar slug empresarial.');
$countries = require dirname(__DIR__) . '/config/europe.php';
$assert(isset($countries['PT'], $countries['DE'], $countries['FR']), 'Configuração de países europeus incompleta.');
$assert(count($countries) >= 30, 'A lista europeia possui poucos países.');
$legal = require dirname(__DIR__) . '/config/legal.php';
foreach (['pt', 'en'] as $language) {
    foreach (['terms', 'privacy', 'cookies', 'data-rights'] as $document) {
        $assert(!empty($legal[$language][$document]['title']), "Documento legal ausente: {$language}/{$document}");
        $assert(!empty($legal[$language][$document]['sections']), "Documento legal sem secções: {$language}/{$document}");
    }
}
$assert(is_file(dirname(__DIR__) . '/database/migrations/008_company_registration_and_onboarding.sql'), 'Migração 008 ausente.');

// Todas as rotas precisam apontar para controllers e métodos existentes.
$routeSource = file_get_contents(dirname(__DIR__) . '/routes/web.php') ?: '';
$assert(str_contains($routeSource, "'/register'"), 'Rota pública de cadastro ausente.');
$assert(str_contains($routeSource, "'/support-project'"), 'Rota de apoio ao projeto ausente.');
$assert(str_contains($routeSource, "'/onboarding'"), 'Rota de onboarding ausente.');
preg_match_all("/\[([A-Za-z]+Controller)::class,\s*'([^']+)'\]/", $routeSource, $matches, PREG_SET_ORDER);
foreach ($matches as $match) {
    $class = 'App\\Controllers\\' . $match[1];
    $assert(class_exists($class), "Controller ausente: {$class}");
    $assert(method_exists($class, $match[2]), "Método ausente: {$class}::{$match[2]}");
}

// Todas as views declaradas pelos controllers precisam existir.
foreach (glob(dirname(__DIR__) . '/app/Controllers/*.php') ?: [] as $controllerFile) {
    $source = file_get_contents($controllerFile) ?: '';
    preg_match_all("/View::render\(\s*'([^']+)'/", $source, $viewMatches);
    foreach ($viewMatches[1] ?? [] as $view) {
        $assert(is_file(dirname(__DIR__) . '/app/Views/' . $view . '.php'), "View ausente: {$view}");
    }
}

// Os dois idiomas precisam ter as mesmas chaves.
$pt = require dirname(__DIR__) . '/resources/lang/pt.php';
$en = require dirname(__DIR__) . '/resources/lang/en.php';
$assert(array_diff_key($pt, $en) === [], 'Existem traduções apenas em português.');
$assert(array_diff_key($en, $pt) === [], 'Existem traduções apenas em inglês.');

if ($failures !== []) {
    fwrite(STDERR, "Falhas encontradas:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "OK: {$checks} verificações concluídas para o TipsForMe v" . app_version() . ".\n";
