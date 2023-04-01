<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/publicevent.bundle.css',
	'js' => 'dist/publicevent.bundle.js',
	'rel' => [
		'ui.vue3',
		'main.date',
		'main.core',
		'main.popup',
		'calendar.util',
		'ui.buttons',
	],
	'skip_core' => false,
];