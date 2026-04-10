<?php

namespace App\Constants;

final class RoleConstants
{
    const ADMIN = '2f0d6b84-3416-11f1-9de3-66de32dbe509';
    const STAFF = '2f0d703e-3416-11f1-9de3-66de32dbe509';
    const VIEWER = '2f0d719c-3416-11f1-9de3-66de32dbe509';

    public static function all(): array
    {
        return [
            self::ADMIN => 'Admin',
            self::STAFF => 'Staff',
            self::VIEWER => 'Viewer',
        ];
    }

    public static function ids(): array
    {
        return array_keys(self::all());
    }
}
