<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/notifications.bundle.css',
	'js' => 'dist/notifications.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.application.core',
		'ui.vue',
		'im.component.notifications',
		'im.provider.pull',
	],
	'skip_core' => true,
];