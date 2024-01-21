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
		'im.v2.lib.layout',
		'im.v2.lib.uuid',
		'im.public',
		'ui.notification',
		'rest.client',
		'main.core.events',
		'ui.uploader.core',
		'im.v2.lib.rest',
		'ui.vue3.vuex',
		'main.core',
		'im.v2.lib.logger',
		'im.v2.lib.utils',
		'im.v2.application.core',
		'im.v2.lib.user',
		'im.v2.const',
	],
	'skip_core' => false,
];