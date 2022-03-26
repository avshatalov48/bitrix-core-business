<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fieldspanel.bundle.css',
	'js' => 'dist/fieldspanel.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.panel.content',
		'main.loader',
		'landing.backend',
		'landing.pageobject',
		'landing.ui.button.sidebarbutton',
		'landing.loc',
		'landing.ui.form.formsettingsform',
		'landing.ui.button.basebutton',
		'landing.ui.field.textfield',
		'landing.ui.panel.formsettingspanel',
		'crm.form.client',
		'ui.userfieldfactory',
	],
	'skip_core' => false,
];