<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/map.bundle.css',
	'js' => 'dist/map.bundle.js',
	'rel' => [
		'main.core.events',
		'landing.collection.basecollection',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];