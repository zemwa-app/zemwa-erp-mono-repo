<?php

$addOnOf = 'worksuite-saas-new';

return [
    'name' => 'Aitools',
    'verification_required' => true,
    'envato_item_id' => 61563898,
    'parent_envato_id' => 23263417,
    'parent_min_version' => '5.5.14',
    'script_name' => $addOnOf . '-aitools-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Aitools\Entities\AiToolsGlobalSetting::class,
];
