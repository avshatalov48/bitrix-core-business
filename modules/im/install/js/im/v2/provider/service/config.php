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
		'im.v2.provider.service',
		'im.v2.lib.uuid',
		'im.public',
		'ui.notification',
		'main.core.events',
		'ui.uploader.core',
		'im.v2.lib.rest',
		'ui.vue3.vuex',
		'main.core',
		'im.v2.lib.logger',
		'im.v2.lib.utils',
		'rest.client',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.user',
	],
	'skip_core' => false,
];