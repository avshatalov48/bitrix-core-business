<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/confirm.bundle.css',
	'js' => 'dist/confirm.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
];