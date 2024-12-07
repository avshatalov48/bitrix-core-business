<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

return [
	'js' => [
		'./dist/core.bundle.js',
	],
	'rel' => [
		'main.core',
		'ui.vue3',
		'ui.vue3.vuex',
		'pull.client',
		'rest.client',
		'im.v2.application.launch',
		'im.v2.model',
		'im.v2.provider.pull',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
	'settings' => [
		'isCloud' => \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'),
	]
];