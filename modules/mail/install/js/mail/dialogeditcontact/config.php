<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/dialogeditcontact.bundle.css',
	'js' => 'dist/dialogeditcontact.bundle.js',
	'rel' => [
		'main.core',
		'mail.sidepanelwrapper',
		'ui.dialogs.messagebox',
		'ui.forms',
	],
	'skip_core' => false,
];