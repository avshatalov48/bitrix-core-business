<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/lazyload.bundle.css',
	'js' => 'dist/lazyload.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];