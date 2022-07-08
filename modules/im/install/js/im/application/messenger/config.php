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
		'main.polyfill.core',
		'im.application.core',
		'im.controller',
		'im.provider.rest',
		'ui.vue',
		'ui.vue.vuex',
		'im.lib.utils',
		'im.component.recent',
		'im.component.dialog',
		'im.component.textarea',
		'pull.component.status',
		'main.core.events',
		'ui.entity-selector',
		'im.const',
		'im.event-handler',
	],
	'skip_core' => true,
];