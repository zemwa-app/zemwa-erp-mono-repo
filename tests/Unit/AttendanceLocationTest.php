<?php

namespace Tests\Unit;

use App\Helper\AttendanceLocation;
use PHPUnit\Framework\TestCase;

class AttendanceLocationTest extends TestCase
{
    public function test_parse_coordinates_accepts_valid_values(): void
    {
        $coords = AttendanceLocation::parseCoordinates('28.6139', '77.2090');

        $this->assertSame(28.6139, $coords['latitude']);
        $this->assertSame(77.2090, $coords['longitude']);
    }

    public function test_parse_coordinates_rejects_empty_and_non_numeric(): void
    {
        $this->assertNull(AttendanceLocation::parseCoordinates('', ''));
        $this->assertNull(AttendanceLocation::parseCoordinates(null, null));
        $this->assertNull(AttendanceLocation::parseCoordinates('abc', '77.2'));
        $this->assertNull(AttendanceLocation::parseCoordinates('28.6', 'xyz'));
    }

    public function test_parse_coordinates_rejects_out_of_range(): void
    {
        $this->assertNull(AttendanceLocation::parseCoordinates('91', '0'));
        $this->assertNull(AttendanceLocation::parseCoordinates('-91', '0'));
        $this->assertNull(AttendanceLocation::parseCoordinates('0', '181'));
        $this->assertNull(AttendanceLocation::parseCoordinates('0', '-181'));
    }

    public function test_location_required_when_save_current_location_enabled(): void
    {
        $settings = (object) [
            'save_current_location' => 1,
            'radius_check' => 'no',
        ];

        $this->assertTrue(AttendanceLocation::isRequired($settings, 'home'));
        $this->assertTrue(AttendanceLocation::isRequired($settings, 'office'));
    }

    public function test_location_required_for_radius_except_home(): void
    {
        $settings = (object) [
            'save_current_location' => 0,
            'radius_check' => 'yes',
        ];

        $this->assertFalse(AttendanceLocation::isRequired($settings, 'home'));
        $this->assertTrue(AttendanceLocation::isRequired($settings, 'office'));
        $this->assertTrue(AttendanceLocation::isRequired($settings, 'other'));
    }

    public function test_location_not_required_when_both_disabled(): void
    {
        $settings = (object) [
            'save_current_location' => 0,
            'radius_check' => 'no',
        ];

        $this->assertFalse(AttendanceLocation::isRequired($settings, 'office'));
    }
}
