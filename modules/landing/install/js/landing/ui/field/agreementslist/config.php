<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/agreementslist.bundle.css',
	'js' => 'dist/agreementslist.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'main.core',
		'main.popup',
		'landing.ui.field.basefield',
		'ui.draganddrop.draggable',
		'landing.ui.field.radiobuttonfield',
		'landing.ui.form.formsettingsform',
		'crm.form.client',
		'landing.ui.component.listitem',
		'landing.ui.component.actionpanel',
		'main.core.events',
		'main.loader',
		'landing.backend',
		'landing.ui.panel.formsettingspanel',
	],
	'skip_core' => false,
];