<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/deletedviewform.bundle.css',
	'js' => 'dist/deletedviewform.bundle.js',
	'rel' => [
		'ui.vue3',
		'calendar.sharing.publicevent',
		'main.core',
		'calendar.util',
	],
	'skip_core' => false,
];