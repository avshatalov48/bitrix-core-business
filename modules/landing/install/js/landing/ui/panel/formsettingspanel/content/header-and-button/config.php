<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/header-and-button.bundle.css',
	'js' => 'dist/header-and-button.bundle.js',
	'rel' => [
		'landing.ui.card.headercard',
		'main.core',
		'landing.ui.card.messagecard',
		'landing.ui.form.formsettingsform',
		'landing.ui.field.textfield',
		'landing.ui.field.variablesfield',
		'landing.ui.panel.basepresetpanel',
		'helper',
	],
	'skip_core' => false,
];