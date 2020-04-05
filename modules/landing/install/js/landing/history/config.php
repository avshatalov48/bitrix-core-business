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
		'landing.main',
		'landing.ui.highlight',
	],
	'skip_core' => false,
];