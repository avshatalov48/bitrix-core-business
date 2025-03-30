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
		'im.v2.lib.feature',
		'im.v2.provider.service',
		'imopenlines.v2.lib.openlines',
		'im.v2.lib.role-manager',
		'im.v2.lib.uuid',
		'im.v2.lib.layout',
		'im.public',
		'im.v2.lib.copilot',
		'im.v2.lib.access',
		'ui.vue3.vuex',
		'ui.notification',
		'main.core',
		'im.v2.lib.user',
		'rest.client',
		'im.v2.lib.utils',
		'main.core.events',
		'ui.uploader.core',
		'im.v2.lib.logger',
		'im.v2.lib.analytics',
		'im.v2.application.core',
		'im.v2.lib.rest',
		'im.v2.const',
	],
	'skip_core' => false,
];