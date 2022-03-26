<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/screenshoter.bundle.css',
	'js' => 'dist/screenshoter.bundle.js',
	'rel' => [
		'landing.pageobject',
		'main.core',
	],
	'skip_core' => false,
];