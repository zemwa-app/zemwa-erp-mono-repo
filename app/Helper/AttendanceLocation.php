<?php

namespace App\Helper;

class AttendanceLocation
{
    /**
     * Whether the request must include valid GPS coordinates.
     */
    public static function isRequired(object $settings, ?string $workFromType): bool
    {
        if (!empty($settings->save_current_location)) {
            return true;
        }

        return ($settings->radius_check ?? null) === 'yes' && $workFromType !== 'home';
    }

    /**
     * Parse and validate latitude/longitude from the client.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public static function parseCoordinates($latitude, $longitude): ?array
    {
        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return null;
        }

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [
            'latitude' => $lat,
            'longitude' => $lng,
        ];
    }
}
