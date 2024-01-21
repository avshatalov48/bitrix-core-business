<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'dist/warehouse-master.bundle.css',
		'/bitrix/components/bitrix/ui.button.panel/templates/.default/style.css',
	],
	'js' => 'dist/warehouse-master.bundle.js',
	'rel' => [
		'ui.vue3',
		'ui.vue3.vuex',
		'main.core.events',
		'main.core',
		'catalog.store-use',
	],
	'skip_core' => false,
];
