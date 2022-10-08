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
		'mail.avatar',
		'ui.forms',
		'ui.alerts',
	],
	'skip_core' => false,
];