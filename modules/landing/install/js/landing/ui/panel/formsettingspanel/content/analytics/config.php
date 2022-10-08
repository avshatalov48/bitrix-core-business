<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/analytics.bundle.css',
	'js' => 'dist/analytics.bundle.js',
	'rel' => [
		'landing.loc',
		'landing.ui.card.headercard',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.field.accordionfield',
		'landing.ui.card.messagecard',
		'ui.design-tokens',
		'main.core',
	],
	'skip_core' => false,
];