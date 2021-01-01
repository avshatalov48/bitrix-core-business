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
		'im_call',
		'im.debug',
		'im.application.launch',
		'im.component.call',
		'im.model',
		'im.controller',
		'im.lib.cookie',
		'im.lib.localstorage',
		'im.lib.logger',
		'im.lib.clipboard',
		'im.lib.uploader',
		'im.lib.desktop',
		'im.const',
		'ui.notification',
		'ui.buttons',
		'ui.progressround',
		'ui.viewer',
		'ui.vue',
		'ui.vue.vuex',
		'main.core',
		'promise',
		'main.date',
		'pull.client',
		'im.provider.pull',
		'rest.client',
		'im.lib.utils',
	],
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_common.php',
	'skip_core' => false,
];