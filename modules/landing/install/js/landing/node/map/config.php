<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/map.bundle.css',
	'js' => 'dist/map.bundle.js',
	'rel' => [
		'main.core',
		'landing.node',
	],
	'skip_core' => false,
];