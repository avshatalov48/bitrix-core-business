<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/counters.bundle.css',
	'js' => 'dist/counters.bundle.js',
	'rel' => [
		'main.core',
		'ui.counterpanel',
		'main.core.events',
	],
	'skip_core' => false,
];