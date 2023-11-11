<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/counter.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.vue3.vuex',
		'im.v2.application.core',
		'im.v2.lib.desktop',
		'im.v2.lib.logger',
		'im.v2.const',
	],
	'skip_core' => false,
];