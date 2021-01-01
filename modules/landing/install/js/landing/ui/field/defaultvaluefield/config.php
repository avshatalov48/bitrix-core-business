<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/defaultvaluefield.bundle.css',
	'js' => 'dist/defaultvaluefield.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.field.basefield',
		'landing.ui.field.datetimefield',
		'landing.ui.component.internal',
		'ui.draganddrop.draggable',
		'landing.loc',
		'landing.ui.component.listitem',
		'main.core.events',
		'landing.ui.panel.fieldspanel',
		'landing.ui.form.formsettingsform',
		'landing.ui.component.actionpanel',
		'landing.ui.field.variablesfield',
	],
	'skip_core' => false,
];