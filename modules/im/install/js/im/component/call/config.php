<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/call.bundle.js',
	],
	'css' => [
		'./dist/call.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.vuex',
		'im.lib.logger',
		'im.const',
		'im.view.textarea',
		'ui.vue.components.smiles',
		'ui.vue',
		'ui.forms',
	],
	'skip_core' => true,
];