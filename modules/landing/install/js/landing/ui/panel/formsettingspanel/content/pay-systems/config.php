<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/pay-systems.bundle.css',
	'js' => 'dist/pay-systems.bundle.js',
	'rel' => [
		'landing.loc',
		'landing.ui.card.headercard',
		'landing.ui.card.messagecard',
		'landing.ui.panel.basepresetpanel',
		'main.core',
		'main.core.events',
		'landing.ui.field.paysystemsselectorfield',
		'landing.ui.form.formsettingsform',
		'landing.ui.panel.formsettingspanel.content.crm.schememanager',
		'ui.sidepanel-content',
	],
	'skip_core' => false,
];