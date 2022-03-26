<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/spam-protection.bundle.css',
	'js' => 'dist/spam-protection.bundle.js',
	'rel' => [
		'landing.ui.card.headercard',
		'landing.loc',
		'landing.ui.field.radiobuttonfield',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.form.formsettingsform',
		'main.core',
		'ui.buttons',
		'landing.ui.panel.formsettingspanel',
		'landing.ui.card.messagecard',
	],
	'skip_core' => false,
];