<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/store-use.bundle.css',
	'js' => 'dist/store-use.bundle.js',
	'rel' => [
		'main.popup',
		'ui.buttons',
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];