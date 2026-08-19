<?php

namespace Tests\Unit;

use App\Helper\AttendanceWorkFrom;
use PHPUnit\Framework\TestCase;

class AttendanceWorkFromTest extends TestCase
{
    public function test_defaults_to_all_types_when_null_or_empty(): void
    {
        $this->assertSame(AttendanceWorkFrom::TYPES, AttendanceWorkFrom::allowedTypes(null));
        $this->assertSame(AttendanceWorkFrom::TYPES, AttendanceWorkFrom::allowedTypes((object) ['allowed_work_from' => null]));
        $this->assertSame(AttendanceWorkFrom::TYPES, AttendanceWorkFrom::allowedTypes((object) ['allowed_work_from' => '[]']));
    }

    public function test_decodes_json_and_filters_invalid_values(): void
    {
        $settings = (object) [
            'allowed_work_from' => json_encode(['office', 'home', 'invalid']),
        ];

        $this->assertSame(['office', 'home'], AttendanceWorkFrom::allowedTypes($settings));
    }

    public function test_accepts_array_value(): void
    {
        $settings = (object) [
            'allowed_work_from' => ['other'],
        ];

        $this->assertSame(['other'], AttendanceWorkFrom::allowedTypes($settings));
    }

    public function test_is_allowed(): void
    {
        $settings = (object) [
            'allowed_work_from' => json_encode(['office']),
        ];

        $this->assertTrue(AttendanceWorkFrom::isAllowed($settings, 'office'));
        $this->assertFalse(AttendanceWorkFrom::isAllowed($settings, 'home'));
        $this->assertFalse(AttendanceWorkFrom::isAllowed($settings, null));
    }
}
