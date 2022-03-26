<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'style.css',
	'js' => 'dist/script.bundle.js',
	'rel' => [
		'main.core',
		'ui.dialogs.messagebox',
		'ui.notification',
		'main.popup',
		'ui.buttons',
		'sidepanel',
		'bp_field_type',
	],
	'skip_core' => false,
];