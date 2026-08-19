<?php

namespace Modules\RestAPI\Entities;

use Modules\RestAPI\Entities\Concerns\ParsesFlexibleDateTimes;

class EmployeeDetails extends \App\Models\EmployeeDetails
{
    use ParsesFlexibleDateTimes;

    protected $with = [];
}
