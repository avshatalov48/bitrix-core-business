<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/formsettingspanel.bundle.css',
	'js' => 'dist/formsettingspanel.bundle.js',
	'rel' => [
		'landing.ui.panel.basepresetpanel',
		'landing.pageobject',
		'landing.loc',
		'main.core',
		'landing.backend',
		'main.loader',
		'crm.form.client',
		'ui.buttons',
		'landing.env',
		'landing.ui.panel.stylepanel',
		'ui.dialogs.messagebox',
		'ui.alerts',
		'landing.ui.button.sidebarbutton',
		'ui.tour',
		'landing.ui.panel.fieldspanel',
		'bitrix24.phoneverify',
		'ui.switcher',
		'ui.hint',
		'ui.fonts.opensans',
		'landing.history',
	],
	'skip_core' => false,
];