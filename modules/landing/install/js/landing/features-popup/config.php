<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/features-popup.bundle.css',
	'js' => 'dist/features-popup.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'main.popup',
		'landing.pageobject',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];