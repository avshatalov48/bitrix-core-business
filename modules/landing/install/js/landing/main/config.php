<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/main.bundle.css',
	'js' => 'dist/main.bundle.js',
	'rel' => [
		'landing.env',
		'landing.loc',
		'landing.ui.panel.content',
		'landing.sliderhacks',
		'landing.pageobject',
		'main.core',
	],
	'skip_core' => false,
];