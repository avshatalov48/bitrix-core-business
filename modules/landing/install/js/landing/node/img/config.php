<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/img.bundle.css',
	'js' => 'dist/img.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.node.base',
		'landing.env',
	],
	'skip_core' => true,
];