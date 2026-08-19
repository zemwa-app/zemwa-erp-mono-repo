<?php

$addOnOf = 'worksuite-saas-new';
$product = $addOnOf . '-onboarding-module';

return [
    'name' => 'Onboarding',
    'verification_required' => true,
    'envato_item_id' => 61235460,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.5.11',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Onboarding\Entities\OnboardingSetting::class,
];
