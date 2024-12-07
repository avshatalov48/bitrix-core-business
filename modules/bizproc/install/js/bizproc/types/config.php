<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/types.bundle.css',
	'js' => 'dist/types.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];