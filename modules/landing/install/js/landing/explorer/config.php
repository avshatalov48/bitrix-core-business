<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/explorer.bundle.css',
	'js' => 'dist/explorer.bundle.js',
	'rel' => [
		'landing.backend',
		'landing.loc',
		'main.popup',
		'ui.dialogs.messagebox',
		'main.core',
		'ui.icons.disk',
	],
	'skip_core' => false,
];
