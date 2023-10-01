<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/source.bundle.css',
	'js' => 'dist/source.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'location.core',
		'location.google',
		'location.osm',
	],
	'skip_core' => true,
];