<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'ProBundle',
    'verification_required' => true,
    'envato_item_id' => 99999999,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.3.4',
    'script_name' => $addOnOf.'-probundle-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\ProBundle\Entities\ProBundleSetting::class,
];
