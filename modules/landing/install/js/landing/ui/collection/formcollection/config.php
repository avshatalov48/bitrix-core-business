<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/formcollection.bundle.css',
	'js' => 'dist/formcollection.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.collection.basecollection',
	],
	'skip_core' => true,
];