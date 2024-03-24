<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/history.bundle.css',
	'js' => 'dist/history.bundle.js',
	'rel' => [
		'landing.main',
		'main.core',
		'landing.backend',
		'landing.pageobject',
		'landing.ui.highlight',
	],
	'skip_core' => false,
];