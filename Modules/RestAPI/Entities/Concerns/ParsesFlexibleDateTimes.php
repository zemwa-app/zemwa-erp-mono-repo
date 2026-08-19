<?php

namespace Modules\RestAPI\Entities\Concerns;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

trait ParsesFlexibleDateTimes
{
    protected function asDateTime($value)
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+$/', $value)) {
            return Carbon::createFromFormat('Y-m-d H:i:s.u', $value);
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+[+-]\d{2}:\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d\TH:i:s.uP', $value);
        }

        try {
            return parent::asDateTime($value);
        } catch (InvalidFormatException) {
            return Carbon::parse($value);
        }
    }
}
