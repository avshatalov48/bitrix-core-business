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
		'im.v2.lib.uuid',
		'im.public',
		'rest.client',
		'ui.notification',
		'ui.vue3.vuex',
		'im.v2.lib.rest',
		'im.v2.provider.service',
		'im.v2.lib.utils',
		'main.core.events',
		'im.v2.lib.uploader',
		'main.core',
		'im.v2.application.core',
		'im.v2.lib.logger',
		'im.v2.const',
		'im.v2.lib.user',
	],
	'skip_core' => false,
];