<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/store-use.bundle.css',
	'js' => 'dist/store-use.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.buttons',
		'main.core',
		'main.popup',
	],
	'skip_core' => false,
];