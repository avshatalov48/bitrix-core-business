<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/design.bundle.css',
	'js' => 'dist/design.bundle.js',
	'rel' => [
		'landing.ui.panel.basepresetpanel',
		'landing.ui.card.headercard',
		'landing.loc',
		'landing.ui.card.messagecard',
		'ui.buttons',
		'main.core',
		'landing.ui.panel.formsettingspanel',
	],
	'skip_core' => false,
];