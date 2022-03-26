<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/default-values.bundle.css',
	'js' => 'dist/default-values.bundle.js',
	'rel' => [
		'landing.ui.panel.basepresetpanel',
		'landing.ui.form.formsettingsform',
		'landing.ui.field.defaultvaluefield',
		'landing.ui.card.headercard',
		'landing.loc',
		'main.core',
		'landing.ui.card.messagecard',
	],
	'skip_core' => false,
];