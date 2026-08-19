<?php

use Modules\Affiliate\Entities\Affiliate;

if (!function_exists('isAffiliate')) {

    function isAffiliate()
    {
        if (!session()->has('isAffiliate')) {
            session(['isAffiliate' => Affiliate::where('user_id', user()->id)->where('status', 'active')->exists()]);
        }

        return session('isAffiliate');
    }

}


if (!function_exists('isUserAffiliate')) {

    function isUserAffiliate($userId)
    {
        return Affiliate::where('user_id', $userId)->where('status', 'active')->exists();
    }

}
