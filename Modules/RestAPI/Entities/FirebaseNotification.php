<?php

namespace Modules\RestAPI\Entities;

use Illuminate\Database\Eloquent\Model;

class FirebaseNotification extends Model
{
    protected $table = 'firebase_notifications';

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}
