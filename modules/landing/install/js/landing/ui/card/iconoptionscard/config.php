<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iconoptionscard.bundle.css',
	'js' => 'dist/iconoptionscard.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.card.basecard',
		'landing.loc',
		'ui.fonts.opensans',
		'landing.ui.panel.iconpanel',
	],
	'skip_core' => false,
];