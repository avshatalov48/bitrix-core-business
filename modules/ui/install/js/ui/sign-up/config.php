<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sign-up.bundle.css',
	'js' => 'dist/sign-up.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.forms',
		'ui.fonts.comforter-brush',
		'main.core',
		'ui.buttons',
		'main.popup',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
];