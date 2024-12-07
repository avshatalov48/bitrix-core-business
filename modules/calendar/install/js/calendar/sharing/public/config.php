<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/public.bundle.css',
	'js' => 'dist/public.bundle.js',
	'rel' => [
		'ui.vue3',
		'main.date',
		'calendar.util',
		'main.core',
		'ui.confetti',
	],
	'skip_core' => false,
];