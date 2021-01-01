<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/listsettingsfield.bundle.css',
	'js' => 'dist/listsettingsfield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'landing.ui.field.textfield',
		'main.core',
	],
	'skip_core' => false,
];