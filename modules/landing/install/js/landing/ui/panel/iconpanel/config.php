<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iconpanel.bundle.css',
	'js' => 'dist/iconpanel.bundle.js',
	'rel' => [
		'landing.ui.panel.content',
		'landing.ui.button.sidebarbutton',
		'landing.ui.card.iconlistcard',
		'landing.ui.button.basebutton',
		'landing.ui.field.textfield',
		'landing.loc',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];