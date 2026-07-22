<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class DataRequest
{
    public function create(int $restaurantId, int $userId, string $type, ?string $details): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO data_requests
                (restaurant_id, user_id, request_type, details, status, requested_at)
             VALUES
                (:restaurant_id, :user_id, :request_type, :details, \'received\', NOW())'
        );
        $statement->execute([
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'request_type' => $type,
            'details' => $details,
        ]);

        return (int) Database::connection()->lastInsertId();
    }
}
