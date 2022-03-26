<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/callback.bundle.css',
	'js' => 'dist/callback.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'landing.loc',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.form.formsettingsform',
		'landing.ui.card.headercard',
		'landing.ui.field.textfield',
		'landing.ui.card.messagecard',
	],
	'skip_core' => false,
];