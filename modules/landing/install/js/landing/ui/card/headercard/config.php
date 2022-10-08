<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/headercard.bundle.css',
	'js' => 'dist/headercard.bundle.js',
	'rel' => [
		'landing.ui.card.basecard',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];