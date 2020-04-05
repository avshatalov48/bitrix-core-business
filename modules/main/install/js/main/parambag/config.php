<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/parambag.bundle.css',
	'js' => 'dist/parambag.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];