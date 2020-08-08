<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/lazyload.bundle.css',
	'js' => 'dist/lazyload.bundle.js',
	'rel' => [
		'main.core',
		'main.polyfill.intersectionobserver',
	],
	'skip_core' => false,
];