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
		'im.v2.lib.layout',
		'im.v2.lib.copilot',
		'im.v2.lib.writing',
		'im.v2.lib.role-manager',
		'im.v2.lib.analytics',
		'ui.vue3.vuex',
		'im.v2.lib.counter',
		'im.public',
		'im.v2.lib.slider',
		'im.v2.lib.utils',
		'im.v2.model',
		'im.v2.lib.channel',
		'im.v2.lib.user',
		'im.v2.lib.desktop-api',
		'im.v2.lib.notifier',
		'im.v2.lib.desktop',
		'im.v2.lib.call',
		'im.v2.lib.local-storage',
		'im.v2.lib.sound-notification',
		'main.core',
		'im.v2.lib.logger',
		'im.v2.provider.pull',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => false,
];