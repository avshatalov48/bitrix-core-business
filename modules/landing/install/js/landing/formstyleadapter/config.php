<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/formstyleadapter.bundle.css',
	'js' => 'dist/formstyleadapter.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'landing.ui.form.styleform',
		'landing.loc',
		'landing.ui.field.colorpickerfield',
		'landing.backend',
		'landing.env',
		'landing.ui.field.color',
		'landing.pageobject',
		'landing.ui.panel.formsettingspanel',
	],
	'skip_core' => false,
];