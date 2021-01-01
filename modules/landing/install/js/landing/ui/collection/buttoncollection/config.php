<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/buttoncollection.bundle.css',
	'js' => 'dist/buttoncollection.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.collection.basecollection',
	],
	'skip_core' => true,
];