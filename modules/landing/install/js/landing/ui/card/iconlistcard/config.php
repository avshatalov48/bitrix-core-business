<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iconlistcard.bundle.css',
	'js' => 'dist/iconlistcard.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'landing.loc',
		'landing.ui.card.basecard',
		'landing.ui.card.iconoptionscard',
	],
	'skip_core' => false,
];