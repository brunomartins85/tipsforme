<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only run in the command line.');
}

require dirname(__DIR__, 2) . '/bootstrap.php';

use App\Core\Database;

function ask(string $question, ?string $default = null): string
{
    $suffix = $default !== null ? " [{$default}]" : '';
    $answer = trim((string) readline($question . $suffix . ': '));

    return $answer !== '' ? $answer : (string) $default;
}

$restaurantName = ask('Restaurant name');
$restaurantSlug = ask('Restaurant slug', strtolower(preg_replace('/[^a-z0-9]+/i', '-', $restaurantName) ?? 'restaurant'));
$adminName = ask('Administrator name');
$adminEmail = text_lower(ask('Administrator email'));
$password = ask('Administrator password');
$language = ask('Language: pt or en', 'pt');

if ($restaurantName === '' || $adminName === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    exit("Invalid data. Use a valid email and a password with at least 8 characters.
");
}

if (!in_array($language, ['pt', 'en'], true)) {
    $language = 'pt';
}

$pdo = Database::connection();
$pdo->beginTransaction();

try {
    $restaurantStatement = $pdo->prepare(
        'INSERT INTO restaurants (name, legal_name, trade_name, slug, onboarding_completed_at) VALUES (:name, :legal_name, :trade_name, :slug, NOW())'
    );
    $restaurantStatement->execute([
        'name' => $restaurantName,
        'legal_name' => $restaurantName,
        'trade_name' => $restaurantName,
        'slug' => $restaurantSlug,
    ]);

    $restaurantId = (int) $pdo->lastInsertId();

    $userStatement = $pdo->prepare(
        'INSERT INTO users (restaurant_id, name, email, password_hash, role, language, email_verified_at)
'
        . 'VALUES (:restaurant_id, :name, :email, :password_hash, :role, :language, NOW())'
    );
    $userStatement->execute([
        'restaurant_id' => $restaurantId,
        'name' => $adminName,
        'email' => $adminEmail,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'admin',
        'language' => $language,
    ]);

    $pdo->commit();
    echo "Restaurant and administrator created successfully.
";
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
