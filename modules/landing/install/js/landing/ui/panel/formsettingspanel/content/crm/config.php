<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/crm.bundle.css',
	'js' => 'dist/crm.bundle.js',
	'rel' => [
		'ui.buttons',
		'landing.ui.card.headercard',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.field.radiobuttonfield',
		'main.core.events',
		'landing.ui.form.formsettingsform',
		'ui.dialogs.messagebox',
		'landing.ui.field.basefield',
		'landing.loc',
		'main.core',
		'landing.ui.panel.formsettingspanel.content.crm.schememanager',
	],
	'skip_core' => false,
];