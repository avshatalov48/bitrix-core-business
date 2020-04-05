<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/highlight.bundle.css',
	'js' => 'dist/highlight.bundle.js',
	'rel' => [
		'main.core',
		'landing.pageobject',
	],
	'skip_core' => false,
];