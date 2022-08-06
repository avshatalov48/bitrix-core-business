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
		'pull.client',
		'ui.vue3.vuex',
		'main.core',
		'main.core.events',
		'im.v2.lib.logger',
		'im.v2.const',
	],
	'skip_core' => false,
];