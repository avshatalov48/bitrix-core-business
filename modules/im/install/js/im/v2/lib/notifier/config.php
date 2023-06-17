<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/notifier.bundle.js',
	],
	'rel' => [
		'ui.notification-manager',
		'ui.vue3.vuex',
		'main.core',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.lib.parser',
		'im.public',
		'im.v2.const',
		'im.v2.provider.service',
	],
	'skip_core' => false,
];
