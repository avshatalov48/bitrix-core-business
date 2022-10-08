<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/basepresetpanel.bundle.css',
	'js' => 'dist/basepresetpanel.bundle.js',
	'rel' => [
		'landing.ui.panel.content',
		'landing.ui.button.basebutton',
		'landing.ui.field.presetfield',
		'landing.pageobject',
		'landing.ui.button.sidebarbutton',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'landing.loc',
		'ui.textcrop',
		'main.loader',
		'main.core.events',
		'main.core',
		'landing.ui.card.headercard',
		'landing.ui.card.messagecard',
		'landing.ui.form.formsettingsform',
		'landing.collection.basecollection',
		'landing.ui.form.baseform',
	],
	'skip_core' => false,
];