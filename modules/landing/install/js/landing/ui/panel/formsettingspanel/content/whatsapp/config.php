<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/whatsapp.bundle.css',
	'js' => 'dist/whatsapp.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'landing.loc',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.form.formsettingsform',
		'landing.ui.card.headercard',
		'landing.ui.field.textfield',
		'landing.ui.card.messagecard',
		'crm.form.client',
		'landing.ui.panel.formsettingspanel',
	],
	'skip_core' => false,
];