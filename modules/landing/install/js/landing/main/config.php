<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/main.bundle.css',
	'js' => 'dist/main.bundle.js',
	'rel' => [
		'main.core.events',
		'landing.env',
		'landing.loc',
		'landing.ui.panel.content',
		'landing.ui.panel.saveblock',
		'landing.sliderhacks',
		'landing.pageobject',
		'main.core',
		'landing.backend',
	],
	'skip_core' => false,
];