<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/nodecollection.bundle.css',
	'js' => 'dist/nodecollection.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.collection.basecollection',
	],
	'skip_core' => true,
];