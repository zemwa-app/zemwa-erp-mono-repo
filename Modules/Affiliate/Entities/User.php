<?php

namespace Modules\Affiliate\Entities;

use App\Models\User as AppUser;

class User extends AppUser
{

    public function affiliate()
    {
        return $this->hasOne(Affiliate::class, 'user_id');
    }

}
