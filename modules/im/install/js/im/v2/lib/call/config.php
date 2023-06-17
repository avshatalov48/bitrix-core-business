<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/call.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'ui.vue3.vuex',
		'im.call',
		'im.public',
		'im.v2.application.core',
		'im.v2.lib.slider',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.lib.sound-notification',
		'im_call_compatible',
	],
	'skip_core' => true,
];