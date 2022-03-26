<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/variablesfield.bundle.css',
	'js' => 'dist/variablesfield.bundle.js',
	'rel' => [
		'landing.ui.field.textfield',
		'main.core',
		'landing.ui.button.basebutton',
		'main.popup',
		'landing.pageobject',
	],
	'skip_core' => false,
];