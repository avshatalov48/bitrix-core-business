<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/map.bundle.css',
	'js' => 'dist/map.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.node.base',
	],
	'skip_core' => true,
];