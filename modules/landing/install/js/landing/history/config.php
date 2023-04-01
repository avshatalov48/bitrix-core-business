<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/history.bundle.css',
	'js' => 'dist/history.bundle.js',
	'rel' => [
		'main.core',
		'landing.pageobject',
		'landing.ui.highlight',
		'landing.main',
	],
	'skip_core' => false,
];