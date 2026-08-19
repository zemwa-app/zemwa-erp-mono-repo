<?php

namespace App\Helper;

class AttendanceWorkFrom
{
    public const TYPES = ['office', 'home', 'other'];

    /**
     * Allowed work-from types from attendance settings (defaults to all).
     *
     * @return list<string>
     */
    public static function allowedTypes(?object $settings): array
    {
        if (!$settings) {
            return self::TYPES;
        }

        $raw = $settings->allowed_work_from ?? null;

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
        } elseif (is_array($raw)) {
            $decoded = $raw;
        } else {
            $decoded = null;
        }

        if (!is_array($decoded) || empty($decoded)) {
            return self::TYPES;
        }

        $allowed = array_values(array_intersect(self::TYPES, $decoded));

        return $allowed !== [] ? $allowed : self::TYPES;
    }

    public static function isAllowed(?object $settings, ?string $type): bool
    {
        if ($type === null || $type === '') {
            return false;
        }

        return in_array($type, self::allowedTypes($settings), true);
    }
}
