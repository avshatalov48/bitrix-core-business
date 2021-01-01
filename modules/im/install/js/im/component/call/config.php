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
		'ui.dialogs.messagebox',
		'im.view.textarea',
		'im.component.dialog',
		'ui.switcher',
		'ui.vue.components.smiles',
		'main.core',
		'im.lib.logger',
		'ui.forms',
		'ui.vue',
		'im.const',
		'im.lib.cookie',
		'im.lib.utils',
		'ui.vue.vuex',
	],
	'skip_core' => false,
];