<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/core.bundle.js',
	],
	'rel' => [
		'im.v2.application.launch',
		'pull.client',
		'rest.client',
		'main.core',
		'ui.vue3',
		'ui.vue3.vuex',
		'im.v2.model',
		'im.v2.const',
		'im.v2.provider.pull',
		'im.v2.lib.logger',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];