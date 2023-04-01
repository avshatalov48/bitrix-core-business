<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/alert.bundle.css',
	'js' => 'dist/alert.bundle.js',
	'rel' => [
		'ui.vue3',
		'main.core',
		'calendar.util',
	],
	'skip_core' => false,
];