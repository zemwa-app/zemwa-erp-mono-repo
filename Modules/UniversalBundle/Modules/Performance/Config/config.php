<?php


$addOnOf = 'worksuite-saas-new';
$product = $addOnOf . '-performance-module';

return [
    'name' => 'Performance',
    'verification_required' => true,
    'envato_item_id' => 56487804,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.4.7',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Performance\Entities\PerformanceGlobalSetting::class,
];
