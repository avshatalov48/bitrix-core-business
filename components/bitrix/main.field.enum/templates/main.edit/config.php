<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/display.bundle.css',
	'js' => 'dist/display.bundle.js',
	'rel' => [
		'ui.entity-selector',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];