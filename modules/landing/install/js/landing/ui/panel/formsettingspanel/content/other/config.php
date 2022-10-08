<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/other.bundle.css',
	'js' => 'dist/other.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'landing.ui.card.headercard',
		'landing.loc',
		'landing.ui.form.formsettingsform',
		'landing.ui.field.textfield',
		'landing.ui.panel.basepresetpanel',
		'main.core.events',
		'landing.ui.field.basefield',
		'ui.entity-selector',
		'landing.pageobject',
		'main.core',
		'landing.ui.card.basecard',
	],
	'skip_core' => false,
];