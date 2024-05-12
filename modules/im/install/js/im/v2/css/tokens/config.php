<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/tokens.bundle.css',
	'js' => 'dist/tokens.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];