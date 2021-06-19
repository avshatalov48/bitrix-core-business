<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/formsettingspanel.bundle.css',
	'js' => 'dist/formsettingspanel.bundle.js',
	'rel' => [
		'landing.backend',
		'main.loader',
		'landing.env',
		'landing.ui.panel.stylepanel',
		'ui.dialogs.messagebox',
		'helper',
		'landing.ui.field.agreementslist',
		'landing.ui.field.accordionfield',
		'landing.ui.field.fieldslistfield',
		'landing.ui.field.rulefield',
		'landing.ui.component.actionpanel',
		'landing.ui.component.internal',
		'landing.ui.field.presetfield',
		'landing.ui.field.variablesfield',
		'landing.ui.component.link',
		'landing.ui.field.radiobuttonfield',
		'landing.ui.field.defaultvaluefield',
		'ui.buttons',
		'landing.ui.card.basecard',
		'crm.form.client',
		'landing.ui.card.messagecard',
		'landing.ui.card.headercard',
		'landing.ui.form.formsettingsform',
		'landing.ui.field.textfield',
		'main.core.events',
		'landing.ui.field.basefield',
		'ui.entity-selector',
		'landing.pageobject',
		'main.core',
		'landing.ui.button.sidebarbutton',
		'landing.loc',
		'landing.ui.panel.basepresetpanel',
	],
	'skip_core' => false,
];