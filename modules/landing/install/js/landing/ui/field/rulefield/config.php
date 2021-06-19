<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rulefield.bundle.css',
	'js' => 'dist/rulefield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'landing.ui.component.actionpanel',
		'main.core',
		'main.core.events',
		'landing.ui.component.iconbutton',
		'landing.loc',
		'main.popup',
		'ui.draganddrop.draggable',
		'landing.pageobject',
		'landing.ui.field.textfield',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];