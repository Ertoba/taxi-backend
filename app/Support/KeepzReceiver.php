<?php

namespace App\Support;

use Illuminate\Support\Str;

final class KeepzReceiver
{
    public const TYPE_IBAN = 'IBAN';

    public const TYPE_BRANCH = 'BRANCH';

    public const TYPE_USER = 'USER';

    public static function normalizeType(?string $type): string
    {
        return strtoupper(trim((string) $type));
    }

    public static function normalizeIdentifier(?string $identifier, ?string $type): string
    {
        $normalizedType = self::normalizeType($type);
        $value = trim((string) $identifier);

        if ($normalizedType === self::TYPE_IBAN) {
            return strtoupper((string) preg_replace('/\s+/', '', $value));
        }

        return strtolower($value);
    }

    public static function isSupportedType(?string $type): bool
    {
        return in_array(self::normalizeType($type), [
            self::TYPE_IBAN,
            self::TYPE_BRANCH,
            self::TYPE_USER,
        ], true);
    }

    public static function isValid(?string $type, ?string $identifier): bool
    {
        $normalizedType = self::normalizeType($type);
        $normalizedIdentifier = self::normalizeIdentifier($identifier, $normalizedType);

        if (! self::isSupportedType($normalizedType) || $normalizedIdentifier === '') {
            return false;
        }

        if ($normalizedType === self::TYPE_IBAN) {
            return (bool) preg_match('/^GE\d{2}[A-Z]{2}\d{16}$/', $normalizedIdentifier);
        }

        return Str::isUuid($normalizedIdentifier);
    }

    public static function mask(?string $type, ?string $identifier): string
    {
        $normalizedType = self::normalizeType($type);
        $normalizedIdentifier = self::normalizeIdentifier($identifier, $normalizedType);

        if ($normalizedIdentifier === '') {
            return '';
        }

        if ($normalizedType === self::TYPE_IBAN && strlen($normalizedIdentifier) >= 8) {
            return substr($normalizedIdentifier, 0, 4)
                .str_repeat('*', max(strlen($normalizedIdentifier) - 8, 4))
                .substr($normalizedIdentifier, -4);
        }

        if (strlen($normalizedIdentifier) <= 12) {
            return str_repeat('*', max(strlen($normalizedIdentifier) - 4, 4))
                .substr($normalizedIdentifier, -4);
        }

        return substr($normalizedIdentifier, 0, 8).'…'.substr($normalizedIdentifier, -4);
    }
}
