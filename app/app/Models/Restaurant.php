<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Restaurant
{
    public function findById(int $restaurantId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                id,
                name,
                legal_name,
                trade_name,
                country_code,
                company_registration_number,
                vat_number,
                business_type,
                address_line1,
                address_line2,
                postal_code,
                city,
                slug,
                currency,
                timezone,
                default_language,
                default_card_fee,
                first_half_closing_day,
                password_reset_enabled,
                onboarding_completed_at
             FROM restaurants
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $restaurantId]);
        $restaurant = $statement->fetch(PDO::FETCH_ASSOC);

        return $restaurant ?: null;
    }

    public function updateSettings(int $restaurantId, array $data): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE restaurants
             SET name = :name,
                 timezone = :timezone,
                 default_language = :default_language,
                 default_card_fee = :default_card_fee,
                 first_half_closing_day = :first_half_closing_day,
                 password_reset_enabled = :password_reset_enabled
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $restaurantId,
            'name' => $data['name'],
            'timezone' => $data['timezone'],
            'default_language' => $data['default_language'],
            'default_card_fee' => $data['default_card_fee'],
            'first_half_closing_day' => $data['first_half_closing_day'],
            'password_reset_enabled' => $data['password_reset_enabled'],
        ]);
    }


    public function updateCompanyProfile(int $restaurantId, array $data): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE restaurants
             SET legal_name = :legal_name,
                 trade_name = :trade_name,
                 name = :display_name,
                 country_code = :country_code,
                 company_registration_number = :company_registration_number,
                 vat_number = :vat_number,
                 business_type = :business_type,
                 address_line1 = :address_line1,
                 address_line2 = :address_line2,
                 postal_code = :postal_code,
                 city = :city
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $restaurantId,
            'legal_name' => $data['legal_name'],
            'trade_name' => $data['trade_name'] ?: null,
            'display_name' => $data['trade_name'] ?: $data['legal_name'],
            'country_code' => $data['country_code'],
            'company_registration_number' => $data['company_registration_number'],
            'vat_number' => $data['vat_number'] ?: null,
            'business_type' => $data['business_type'],
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'] ?: null,
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
        ]);
    }

    public function completeOnboarding(int $restaurantId, array $data): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE restaurants
             SET default_card_fee = :default_card_fee,
                 first_half_closing_day = :first_half_closing_day,
                 timezone = :timezone,
                 default_language = :default_language,
                 onboarding_completed_at = NOW()
             WHERE id = :id
               AND status = \'active\''
        );

        return $statement->execute([
            'id' => $restaurantId,
            'default_card_fee' => $data['default_card_fee'],
            'first_half_closing_day' => $data['first_half_closing_day'],
            'timezone' => $data['timezone'],
            'default_language' => $data['default_language'],
        ]);
    }

}
