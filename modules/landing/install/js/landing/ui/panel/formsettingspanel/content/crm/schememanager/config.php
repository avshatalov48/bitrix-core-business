<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/schememanager.bundle.css',
	'js' => 'dist/schememanager.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];