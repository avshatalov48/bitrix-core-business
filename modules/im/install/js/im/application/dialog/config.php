<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/dialog.bundle.js',
	],
	'css' =>[
		'./dist/dialog.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.application.core',
		'im.provider.rest',
		'promise',
		'pull.client',
		'ui.vue',
		'im.lib.logger',
		'im.lib.utils',
		'im.component.recent',
		'im.component.dialog',
		'im.component.textarea',
		'pull.component.status',
		'im.const',
		'im.mixin',
		'main.core.events',
	],
	'skip_core' => true,
];