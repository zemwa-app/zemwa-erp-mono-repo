<?php

namespace Modules\ProBundle\Entities;

use App\Models\BaseModel;

class ProBundleSetting extends BaseModel
{
    protected $guarded = ['id'];

    const MODULE_NAME = 'probundle';
}
