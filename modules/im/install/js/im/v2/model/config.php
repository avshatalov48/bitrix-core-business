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
		'im.v2.lib.logger',
		'ui.reactions-select',
		'im.v2.application.core',
		'im.v2.lib.utils',
		'main.core',
		'ui.vue3.vuex',
		'im.v2.const',
	],
	'skip_core' => false,
];