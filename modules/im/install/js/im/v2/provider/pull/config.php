<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/registry.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'im.v2.lib.uuid',
		'im.v2.provider.service',
		'im.public',
		'im.v2.lib.writing',
		'ui.vue3.vuex',
		'im.v2.lib.counter',
		'im.v2.lib.user',
		'im.v2.lib.desktop-api',
		'im.v2.const',
		'im.v2.lib.notifier',
		'im.v2.lib.desktop',
		'im.v2.lib.call',
		'im.v2.lib.local-storage',
		'im.v2.lib.sound-notification',
		'im.v2.lib.logger',
		'main.core',
		'im.v2.application.core',
	],
	'skip_core' => false,
];