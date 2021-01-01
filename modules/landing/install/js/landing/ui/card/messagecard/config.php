<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/messagecard.bundle.css',
	'js' => 'dist/messagecard.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.card.basecard',
		'landing.loc',
	],
	'skip_core' => false,
];