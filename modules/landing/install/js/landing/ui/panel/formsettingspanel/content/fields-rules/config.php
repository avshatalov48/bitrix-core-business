<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fields-rules.bundle.css',
	'js' => 'dist/fields-rules.bundle.js',
	'rel' => [
		'landing.ui.panel.basepresetpanel',
		'landing.ui.card.headercard',
		'landing.ui.field.radiobuttonfield',
		'landing.ui.form.formsettingsform',
		'ui.fonts.opensans',
		'landing.ui.field.basefield',
		'landing.ui.component.iconbutton',
		'main.popup',
		'landing.pageobject',
		'landing.ui.field.textfield',
		'ui.design-tokens',
		'main.core.events',
		'main.core',
		'landing.ui.component.internal',
		'landing.ui.component.actionpanel',
		'landing.loc',
	],
	'skip_core' => false,
];