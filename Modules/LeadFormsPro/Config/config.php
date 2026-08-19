<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'LeadFormsPro',
    'verification_required' => false,
    'envato_item_id' => '',
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.2.5',
    'script_name' => $addOnOf.'-leadformspro',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\LeadFormsPro\Entities\LeadFormsProSetting::class,
];
