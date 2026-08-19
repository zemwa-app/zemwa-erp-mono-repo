<?php

$addOnOf = 'worksuite-saas-new';

return [
	'name' => 'TrainingPro',
	'verification_required' => false,
	'envato_item_id' => '',
	'parent_envato_id' => 23263417,
	'parent_min_version' => '5.2.5',
	'script_name' => $addOnOf.'-trainingpro',
	'parent_product_name' => $addOnOf,
	'setting' => \Modules\TrainingPro\Entities\TrainingProSetting::class,
];
