<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/message-notifier.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.vue3.vuex',
		'ui.notification-manager',
		'im.v2.lib.sound-notification',
		'im.v2.application.core',
		'im.v2.lib.desktop',
		'im.v2.lib.desktop-api',
		'im.public',
		'im.v2.provider.service.notification',
		'im.v2.const',
		'im.v2.lib.parser',
	],
	'skip_core' => false,
];
