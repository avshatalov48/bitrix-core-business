<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/domrect.bundle.css',
	'js' => 'dist/domrect.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];