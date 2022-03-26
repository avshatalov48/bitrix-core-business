<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fields.bundle.css',
	'js' => 'dist/fields.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
		'landing.ui.card.headercard',
		'landing.ui.card.messagecard',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.form.formsettingsform',
		'landing.ui.field.fieldslistfield',
	],
	'skip_core' => true,
];