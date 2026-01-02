<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/chat.bundle.js',
	],
	'rel' => [
		'imopenlines.v2.lib.openlines',
		'call.lib.call-token-manager',
		'im.public',
		'im.v2.provider.service.message',
		'im.v2.lib.copilot',
		'im.v2.lib.user',
		'im.v2.lib.analytics',
		'ui.uploader.core',
		'im.v2.lib.role-manager',
		'im.v2.lib.uuid',
		'ui.vue3.vuex',
		'rest.client',
		'im.v2.lib.utils',
		'im.v2.lib.notifier',
		'im.v2.lib.layout',
		'main.core',
		'im.v2.application.core',
		'im.v2.lib.logger',
		'im.v2.const',
		'im.v2.lib.rest',
		'im.v2.lib.feature',
	],
	'skip_core' => false,
];
