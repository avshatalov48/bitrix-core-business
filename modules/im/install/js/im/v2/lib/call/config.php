<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/call.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'ui.vue3.vuex',
		'call.core',
		'im.public',
		'im.v2.lib.slider',
		'im.v2.lib.logger',
		'im.v2.lib.promo',
		'im.v2.lib.sound-notification',
		'im.v2.lib.rest',
		'im.v2.const',
		'main.core',
		'ui.entity-selector',
		'ui.buttons',
		'im.v2.application.core',
		'im_call_compatible',
	],
	'skip_core' => false,
	'settings' => [
		'callInstalled' => \Bitrix\Main\ModuleManager::isModuleInstalled('call'),
	],
];