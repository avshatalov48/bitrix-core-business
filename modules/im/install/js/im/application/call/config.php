<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/call.bundle.js',
	],
	'css' =>[
		'./dist/call.bundle.css',
	],
	'rel' => [
		'im.lib.localstorage',
		'im_call',
		'im.debug',
		'im.lib.clipboard',
		'ui.notification',
		'ui.buttons',
		'im.provider.pull',
		'main.core',
		'promise',
		'pull.client',
		'rest.client',
		'im.lib.utils',
		'ui.vue',
		'im.component.dialog',
		'im.component.call',
		'pull.component.status',
		'im.const',
		'im.lib.cookie',
		'im.model',
		'ui.vue.vuex',
		'im.controller',
		'im.application.launch',
	],
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_common.php',
	'skip_core' => false,
];