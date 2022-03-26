<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/vk.bundle.css',
	'js' => 'dist/vk.bundle.js',
	'rel' => [
		'landing.ui.panel.basepresetpanel',
		'landing.ui.card.headercard',
		'landing.loc',
		'landing.ui.card.basecard',
		'main.core',
		'main.core.events',
		'landing.ui.card.messagecard',
		'crm.form.integration',
	],
	'skip_core' => false,
];