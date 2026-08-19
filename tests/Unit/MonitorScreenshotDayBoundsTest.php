<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Modules\Monitor\Services\MonitorScreenshotService;
use PHPUnit\Framework\TestCase;

/**
 * Regression: Asia/Kolkata morning screenshots must fall inside the selected local day.
 * The old path compared company-local wall strings to UTC captured_at and excluded
 * shots before ~11:00 IST (customers reported this as "before 12pm").
 */
class MonitorScreenshotDayBoundsTest extends TestCase
{
    public function test_kolkata_morning_screenshot_is_inside_local_day_utc_bounds(): void
    {
        [$dayStartUtc, $dayEndUtc] = MonitorScreenshotService::dayBoundsUtc('2026-07-27', 'Asia/Kolkata');

        // 09:00 IST = 03:30 UTC
        $morningShotUtc = Carbon::parse('2026-07-27 03:30:00', 'UTC');

        $this->assertTrue(
            $morningShotUtc->betweenIncluded($dayStartUtc, $dayEndUtc),
            sprintf(
                'Expected 09:00 IST shot inside bounds [%s, %s]',
                $dayStartUtc->toDateTimeString(),
                $dayEndUtc->toDateTimeString()
            )
        );

        // Local midnight IST = previous day 18:30 UTC
        $this->assertSame('2026-07-26 18:30:00', $dayStartUtc->toDateTimeString());
        $this->assertSame('2026-07-27 18:29:59', $dayEndUtc->format('Y-m-d H:i:s'));
    }

    public function test_old_broken_bounds_would_exclude_kolkata_morning_shot(): void
    {
        // Documents the failure mode we fixed: parse date as app/UTC midnight, then
        // shift to company TZ and emit a wall-time string compared to UTC DB values.
        $date = Carbon::parse('2026-07-27'); // midnight UTC when app TZ is UTC
        $brokenLower = $date->copy()->timezone('Asia/Kolkata')->toDateTimeString();
        $morningShotUtc = '2026-07-27 03:30:00';

        $this->assertSame('2026-07-27 05:30:00', $brokenLower);
        $this->assertTrue($morningShotUtc < $brokenLower);
    }
}
