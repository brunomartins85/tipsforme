<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class CompanyRegistration
{
    public function emailExists(string $email): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => text_lower(trim($email))]);

        return (bool) $statement->fetchColumn();
    }

    public function create(array $data): array
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $slug = $this->uniqueSlug($connection, (string) ($data['trade_name'] ?: $data['legal_name']));

            $restaurantStatement = $connection->prepare(
                'INSERT INTO restaurants
                    (
                        name, legal_name, trade_name, slug, status, currency, timezone,
                        default_language, default_card_fee, first_half_closing_day,
                        password_reset_enabled, country_code, company_registration_number,
                        vat_number, business_type, address_line1, address_line2, postal_code,
                        city, terms_accepted_at, terms_version, privacy_acknowledged_at,
                        privacy_version, marketing_consent_at, registration_ip
                    )
                 VALUES
                    (
                        :name, :legal_name, :trade_name, :slug, \'pending_verification\', \'EUR\', :timezone,
                        :default_language, 25.00, 15, 1, :country_code, :company_registration_number,
                        :vat_number, :business_type, :address_line1, :address_line2, :postal_code,
                        :city, NOW(), :terms_version, NOW(), :privacy_version, :marketing_consent_at,
                        :registration_ip
                    )'
            );
            $restaurantStatement->execute([
                'name' => $data['trade_name'] ?: $data['legal_name'],
                'legal_name' => $data['legal_name'],
                'trade_name' => $data['trade_name'] ?: null,
                'slug' => $slug,
                'timezone' => $data['timezone'],
                'default_language' => $data['language'],
                'country_code' => $data['country_code'],
                'company_registration_number' => $data['company_registration_number'],
                'vat_number' => $data['vat_number'] ?: null,
                'business_type' => $data['business_type'],
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?: null,
                'postal_code' => $data['postal_code'],
                'city' => $data['city'],
                'terms_version' => $data['terms_version'],
                'privacy_version' => $data['privacy_version'],
                'marketing_consent_at' => $data['marketing_consent'] ? date('Y-m-d H:i:s') : null,
                'registration_ip' => $data['registration_ip'],
            ]);
            $restaurantId = (int) $connection->lastInsertId();

            $userStatement = $connection->prepare(
                'INSERT INTO users
                    (restaurant_id, name, email, password_hash, role, language, status, email_verified_at)
                 VALUES
                    (:restaurant_id, :name, :email, :password_hash, \'admin\', :language, \'active\', NULL)'
            );
            $userStatement->execute([
                'restaurant_id' => $restaurantId,
                'name' => $data['admin_name'],
                'email' => text_lower(trim($data['admin_email'])),
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'language' => $data['language'],
            ]);
            $userId = (int) $connection->lastInsertId();

            $connection->commit();

            return [
                'restaurant_id' => $restaurantId,
                'user_id' => $userId,
                'slug' => $slug,
            ];
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private function uniqueSlug(PDO $connection, string $name): string
    {
        $baseSlug = slugify($name);

        if ($baseSlug === '') {
            $baseSlug = 'company';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($connection, $slug)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(PDO $connection, string $slug): bool
    {
        $statement = $connection->prepare(
            'SELECT id FROM restaurants WHERE slug = :slug LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);

        return (bool) $statement->fetchColumn();
    }
}
