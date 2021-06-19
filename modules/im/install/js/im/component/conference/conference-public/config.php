<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/conference-public.bundle.js',
	],
	'css' => [
		'./dist/conference-public.bundle.css',
	],
	'rel' => [
		'im.mixin',
		'im.component.dialog',
		'im.component.textarea',
		'ui.switcher',
		'ui.vue.components.smiles',
		'main.core',
		'ui.forms',
		'im.lib.cookie',
		'im.component.call-feedback',
		'im.lib.desktop',
		'ui.vue',
		'im.lib.logger',
		'main.core.events',
		'im.const',
		'im.lib.utils',
		'ui.vue.vuex',
		'main.popup',
		'im.lib.clipboard',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
];