<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fieldslistfield.bundle.css',
	'js' => 'dist/fieldslistfield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'landing.loc',
		'main.core',
		'ui.draganddrop.draggable',
		'landing.ui.panel.fieldspanel',
		'landing.ui.component.listitem',
		'landing.ui.component.actionpanel',
		'landing.ui.field.textfield',
		'main.core.events',
		'landing.ui.form.formsettingsform',
		'crm.form.client',
		'landing.ui.field.listsettingsfield',
		'landing.ui.panel.separatorpanel',
		'landing.pageobject',
		'main.loader',
		'landing.ui.field.productfield',
		'calendar.resourcebookinguserfield',
		'socnetlogdest',
		'ui.hint',
		'landing.ui.component.iconbutton',
	],
	'skip_core' => false,
];