<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/actions.bundle.css',
	'js' => 'dist/actions.bundle.js',
	'rel' => [
		'landing.ui.card.headercard',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.field.radiobuttonfield',
		'main.core.events',
		'landing.ui.field.presetfield',
		'landing.ui.field.textfield',
		'ui.design-tokens',
		'landing.ui.field.basefield',
		'landing.ui.component.internal',
		'landing.ui.card.messagecard',
		'main.core',
		'landing.loc',
	],
	'skip_core' => false,
];