<?php

$addOnOf = 'worksuite-saas-new';
$product = $addOnOf . '-policy-module';

return [
    'name' => 'Policy',
    'verification_required' => true,
    'envato_item_id' => 61118364,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.2.11',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Policy\Entities\PolicySetting::class,
];
