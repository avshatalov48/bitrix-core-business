<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/stylepanel.bundle.css',
	'js' => 'dist/stylepanel.bundle.js',
	'rel' => [
		'main.core',
		'main.loader',
		'landing.ui.panel.content',
		'landing.loc',
		'landing.pageobject',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];