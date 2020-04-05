<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/dialogs.bundle.css',
	'js' => 'dist/dialogs.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.buttons',
	],
	'skip_core' => false,
];