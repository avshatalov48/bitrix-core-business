<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/messenger.bundle.js',
	],
	'css' =>[
		'./dist/messenger.bundle.css',
	],
	'rel' => [
		'im.application.core',
		'im.provider.rest',
		'promise',
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
		'main.core',
	],
	'skip_core' => false,
];