<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/datepick.bundle.css',
	'js' => 'dist/datepick.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.vue',
	],
	'skip_core' => false,
];